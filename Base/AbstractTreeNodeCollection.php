<?php

namespace Iliich246\YicmsEssences\Base;

use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Languages\LanguagesDb;
use Iliich246\YicmsEssences\EssencesModule;

/**
 * Class AbstractTreeNodeCollection
 *
 * This class provide functionality of collection of the tree nodes.
 * This class works only with data base, he fetch data and build tree structure
 *
 * @author iliich246 <iliich246@gmail.com>
 */
abstract class AbstractTreeNodeCollection extends ActiveRecord
{
    const EVENT_LIST_DATA_INSERT = 'listDataInsert';

    //Style of sort
    const SORT_NO   = 0x00;
    const SORT_ASC  = 0x01;
    const SORT_DESC = 0x02;

    /** @var array with tree nodes represented in tree view
     * It`s represented as:
     * [1] => array -> this index is node id in database
     *   (
     *      [node] => instance of AbstractDbTreeNode
     *      [children] => array -> array of children of this node
     *          (
     *              [7] => array
     *                  (
     *                      [node] => AbstractTreeNode
     *                  )
     *              [8] => array
     *                  (
     *                      [node] => AbstractTreeNode
     *                      [children] => array
     *                          (
     *                             [12] => Array
     *                                  (
     *                                      [node] => AbstractTreeNode
     *                                  )
     *                              [13] => Array
     *                                  (
     *                                      [node] => AbstractTreeNode
     *                                      [children] => array
     *                                          (
     *                                              ...
     *                                          )
     *                                  )
     *                          )
     *                  )
     *          )
     *    )
     */
    private $treeStructure = null;
    /** @var null|array buffer of nodes fetched from db
     * used for buffered return of nodes by id from linear list
     * instead recursive calls from special tree structure
     */
    private $nodesBuffer = null;
    /** @var null|array buffer for tree order */
    private $treeBuffer = null;
    /** @var int keep style of sorting tree nodes */
    private $sortStyle = self::SORT_ASC;
    /** @var array debug form of tree array */
    private $debug;

    /**
     * Sets style of nodes sort
     * (use self::SORT_<type>)
     * @param $sortStyle
     */
    public function setSortStyle($sortStyle)
    {
        $this->sortStyle = $sortStyle;
    }

    /**
     * Return buffered AbstractTreeNode by ID
     * @param $id
     * @return null|AbstractTreeNode
     */
    protected function getNodeById($id)
    {
        if (is_null($this->treeStructure)) {
            if (isset($this->nodesBuffer[$id]))
                return $this->nodesBuffer[$id];

            return $this->nodesBuffer[$id] = $this->getTreeNode($id);
        }

        if (is_null($this->nodesBuffer))
            $this->getTreeArray();

        if (!isset($this->nodesBuffer[$id])) return null;//may be empty category

        return $this->nodesBuffer[$id];
    }

    /**
     * Return array of tree structure
     * @return array
     */
    public function getTreeArray()
    {
        if (!(is_null($this->treeStructure))) return $this->treeStructure;

        /** @var AbstractTreeNode[] $nodes */
        $nodes = $this->getTreeNodes();

        foreach($nodes as $node)
            $this->nodesBuffer[$node->id] = $node;

        if (!$this->nodesBuffer) {
            $this->treeStructure = [];
            return $this->treeStructure;
        }

        $parentNodes = [];

        foreach ($nodes as $node) {
            $parentNodes[$node->parent_id][$node->id] = $node;
            $node->setCollection($this);
        }

        $arr = [];
        foreach ($parentNodes as $key => $parentNode) {
            uasort($parentNode, function($nodeA, $nodeB) {
                /** @var $nodeA AbstractTreeNode */
                /** @var $nodeB AbstractTreeNode */
                $fieldName = $nodeA->getSortFieldName();

                if ($nodeA->$fieldName == $nodeB->$fieldName)
                    return 0;

                return ($nodeA->$fieldName < $nodeB->$fieldName) ? -1 : 1;
            });

            $arr[$key] = $parentNode;
        }

        $parentNodes = $arr;

        $tree = [];

        foreach($parentNodes[0] as $topNode)
            $tree += $this->buildTree($parentNodes, [$topNode]);

        $this->treeStructure = $tree;

        return $this->treeStructure;
    }

    /**
     * Recursive method for build tree array from nodes
     * @param $parentNodes
     * @param $currentParent
     * @return array
     */
    private function buildTree($parentNodes, $currentParent)
    {
        $tree = [];

        /** @var AbstractTreeNode $parentNode */
        foreach ($currentParent as $parentNode) {
            $ar = null;

            if (isset($parentNodes[$parentNode->id])) {
                $ar['node'] = null; //It is convenient that the node is the first element of the array
                $ar['children'] = $this->buildTree($parentNodes, $parentNodes[$parentNode->id]);
            }

            $ar['node'] = $parentNode;
            $tree[$parentNode->id] = $ar;
        }

        return $tree;
    }

    /**
     * Return list of nodes by tree order
     * @return AbstractTreeNode[]
     */
    protected function traversalByTreeOrder()
    {
        if (!is_null($this->treeBuffer)) return $this->treeBuffer;

        $tree = $this->getTreeArray();

        if (!$tree) return [];

        $result = [];
        foreach($tree as $id=>$topNode) {
            $result += $this->recursiveTraversalByTreeOrder($topNode, 1);
        }

        return $this->treeBuffer = $result;
    }

    /**
     * Recursive method for traversal by tree order
     * @param array $nodeArray
     * @param $level
     * @return mixed
     */
    private function recursiveTraversalByTreeOrder(array $nodeArray, $level)
    {
        /** @var AbstractTreeNode $node */
        $node = $nodeArray['node'];

        $result[$node->id] = $node;

        if (!isset($nodeArray['children'])) return $result;

        $level++;

        foreach($nodeArray['children'] as $id => $childrenArray)
            $result += $this->recursiveTraversalByTreeOrder($childrenArray, $level);

        return $result;
    }

    /**
     * Only for debug purposes
     * @return array
     */
    public function treeDebugForm()
    {
        $this->getTreeArray();

        $this->debug = $this->treeStructure;

        foreach($this->debug as $key => $nodeArray) {

            $this->debug[$key]['p'] = $this->debug[$key]['node']->parent_id;
            $this->debug[$key]['o'] = $this->debug[$key]['node']->category_order;
            $this->debug[$key]['node'] = $this->debug[$key]['node']->id;

            if (isset($nodeArray['children'])) {
                $this->debug[$key]['children'] = $this->treeDebugFormRecursive($nodeArray['children']);
            } else {
                $this->debug[$key]['node'] = null;
            }
        }

        return $this->debug;
    }

    /**
     * Only for debug purposes
     * @param $array
     * @return mixed
     */
    private function treeDebugFormRecursive($array)
    {
        foreach($array as $key => $nodeArray) {

            $array[$key]['p'] = $array[$key]['node']->parent_id;
            $array[$key]['o'] = $array[$key]['node']->category_order;
            $array[$key]['node'] = $array[$key]['node']->id;

            if (isset($nodeArray['children'])) {
                $array[$key]['children'] = $this->treeDebugFormRecursive($nodeArray['children']);
            }
        }

        return $array;
    }

    /**
     * Returns array of nodes for this tree collection (fetch from db)
     * @return AbstractTreeNode[]
     */
    abstract protected function getTreeNodes();

    /**
     * @param $id
     * @return AbstractTreeNode
     */
    abstract protected function getTreeNode($id);
}
