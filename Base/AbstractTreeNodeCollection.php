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
     * Return array of tree structure
     * @return array
     */
    public function getTreeArray()
    {
        if (!(is_null($this->treeStructure))) return $this->treeStructure;

        $nodes = $this->getTreeNodes();

        if (!$nodes) {
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
        foreach($parentNodes[0] as $topNode) {
            $oneTree = $this->buildTree($parentNodes, [$topNode]);
            $tree += $oneTree;
        }

        $this->treeStructure = $tree;

        return $this->treeStructure;
    }

    /**
     * Only for debug purposes
     * @return array
     */
    public function treeDebugForm()
    {
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
            //throw new \Exception(print_r($nodeArray,true));

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
     * Returns array representing current tree for dropdown lists
     * @param LanguagesDb|null $language
     * @return array
     */
    public function getList(LanguagesDb $language = null)
    {
        $tree = $this->getTreeArray();

        $result[0] = EssencesModule::t('app', 'Root category');

        if (!$tree) return $result;
        
        if ($this->getMaxCategoriesLevel() == 1) return $result;

        foreach($tree as $id=>$topNode) {
            $result += $this->traversalForList($topNode, 1, $language);
        }

        return $result;
    }

    /**
     * Recursive method for build array for drop lists
     * @param array $nodeArray
     * @param $level
     * @param LanguagesDb|null $language
     * @return mixed
     */
    private function traversalForList(array $nodeArray, $level, LanguagesDb $language = null)
    {
        /** @var AbstractTreeNode $node */
        $node = $nodeArray['node'];

        $levelString = '';
        for ($i = 1; $i < $level; $i++)
            $levelString .= '-';

        $result[$node->id] = $levelString . $node->getNodeName($language);

        if (!isset($nodeArray['children'])) return $result;

        $level++;

        if ($this->getMaxCategoriesLevel() != 0 && $this->getMaxCategoriesLevel() < ($level+1)) return $result;

        foreach($nodeArray['children'] as $childrenArray)
            $result += $this->traversalForList($childrenArray, $level, $language);

        return $result;
    }

    /**
     * Return list of nodes by tree order
     * @return AbstractTreeNode[]
     */
    public function traversalByTreeOrder()
    {
        $tree = $this->getTreeArray();

        if (!$tree) return [];

        $result = [];
        foreach($tree as $id=>$topNode) {
            $result += $this->recursiveTraversalByTreeOrder($topNode, 1);
        }

        return $result;
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

        //if ($this->getMaxLevelBuffered() !== false && $this->getMaxLevelBuffered() < $level) return $result;

        foreach($nodeArray['children'] as $id => $childrenArray)
            $result += $this->recursiveTraversalByTreeOrder($childrenArray, $level);

        return $result;
    }

    /**
     * Returns maximum allowed level of tree deepness
     * @return int
     */
    abstract public function getMaxCategoriesLevel();

    /**
     * Returns array of nodes for this tree collection (fetch from db)
     * @return AbstractTreeNode[]
     */
    abstract protected function getTreeNodes();
}
