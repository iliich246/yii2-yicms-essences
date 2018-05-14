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

        $tree = [];
        foreach($parentNodes[0] as $topNode) {
            $oneTree = $this->buildTree($parentNodes, [$topNode]);
            $tree += $oneTree;
        }

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

        $result[$node->id] = $node->getNodeName($language);

        if (!isset($nodeArray['children'])) return $result;

        $level++;

        //if ($this->getMaxLevelBuffered() !== false && $this->getMaxLevelBuffered() < $level) return $result;

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
     * Returns array of nodes for this tree collection (fetch from db)
     * @return AbstractTreeNode[]
     */
    abstract protected function getTreeNodes();
}
