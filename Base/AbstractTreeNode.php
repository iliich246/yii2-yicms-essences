<?php

namespace Iliich246\YicmsEssences\Base;

use Yii;
use yii\db\ActiveRecord;
use Iliich246\YicmsCommon\Languages\LanguagesDb;

/**
 * Class AbstractTreeNode
 *
 * This class keep methods for work with database tree node.
 *
 * @property integer $id
 * @property integer $parent_id
 * @property integer $level
 *
 * @author iliich246 <iliich246@gmail.com>
 */
abstract class AbstractTreeNode extends ActiveRecord
{
    /** @var AbstractTreeNodeCollection instance that keep this node */
    protected $collection = null;
    /** @var integer level of node in tree structure  */
    private $level = null;
    /** @var null|array of node block in tree collection (only for buffer purposes) */
    private $nodeBlock = null;
    /** @var null|self parent of this node */
    private $parent = null;

    /**
     * Sets collection that keep this node
     * @param AbstractTreeNodeCollection $collection
     */
    public function setCollection(AbstractTreeNodeCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Returns AbstractTreeNodeCollection for this node
     * @return AbstractTreeNodeCollection
     */
    protected function getCollection()
    {
        return $this->collection;
    }

    /**
     * Returns true, if node has children
     * @return bool
     */
    public function isChildren()
    {
        $nodeBlock = $this->findNodeBlockInCollection();

        if (isset($nodeBlock['children'])) return true;
        return false;
    }

    /**
     * Returns array of children of this node, if none returns empty array
     * @return array
     */
    public function getChildren()
    {
        $nodeBlock = $this->findNodeBlockInCollection();

        if (isset($nodeBlock['children'])) {
            $result = [];

            foreach ($nodeBlock['children'] as $node)
                $result[] = $node['node'];

            return $result;
        }

        return [];
    }

    /**
     * Return true, if node has parent (not top node)
     * @return bool
     */
    public function isParent()
    {
        if (is_null($this->parent)) return false;

        return true;
    }

    /**
     * Sets parent node for this node
     * @param AbstractTreeNode $parent
     */
    public function setParent(AbstractTreeNode $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Returns parent node of this node
     * @return AbstractTreeNode|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Return array of parents from this element to top
     * @return array
     * @throws EssencesException
     */
    public function getParentsToTop()
    {
        $currentParent = $this->getParent();

        if (is_null($currentParent)) return [];

        $parentsArray = [];

        $overflowCounter = 0;

        while(true) {
            if ($overflowCounter++ > 100)
                throw new EssencesException('Overflow of getParentsToTop method');

            $parentsArray[$currentParent->id] = $currentParent;

            if (!$currentParent->isParent()) return array_reverse($parentsArray, true);

            $currentParent = $currentParent->getParent();
        }

        throw new EssencesException('Can`t reach this place 0_0');
    }

    /**
     * Find node block in collection
     * @return array|bool|null
     */
    private function findNodeBlockInCollection()
    {
        if (!is_null($this->nodeBlock)) return $this->nodeBlock;

        foreach($this->collection->getTreeArray() as $id => $nodeBlock) {

            if ($this->id == $id) return $nodeBlock;

            if (isset($nodeBlock['children'])) {
                if ($result = $this->findNodeBlockRecursive($nodeBlock['children']))
                    return $this->nodeBlock = $result;
            }
        }

        return false;
    }

    /**
     * Find node block recursive method
     * @param $nodeBlockParam
     * @return bool
     */
    private function findNodeBlockRecursive($nodeBlockParam)
    {
        foreach($nodeBlockParam as $id => $nodeBlock) {
            if ($this->id == $id) return $nodeBlock;

            if (isset($nodeBlock['children'])) {
                if ($result = $this->findNodeBlockRecursive($nodeBlock['children']))
                    return $result;
            }
        }

        return false;
    }

    /**
     * Returns level of node in tree collection
     * @return bool|int
     * @throws EssencesException
     */
    public function getLevel()
    {
        if (!is_null($this->level)) return $this->level;

        if (!$this->collection) {
            Yii::error("Wrong initialization of tree node, is`s must aggregate collection before", __METHOD__);
            throw new EssencesException("Wrong initialization of tree node, is`s must aggregate collection before");
        }

        //if calculated element is top node
        foreach ($this->collection->getTreeArray() as $id => $topNode) {
            if ($this->id === $id) {
                $this->level = 0;
                return $this->level;
            }
        }

        foreach ($this->collection->getTreeArray() as $topNode) {
            if (isset($topNode['children']))
                if ($result = $this->recursiveLevel($topNode['children'], 1)) return $result;
        }

        throw new EssencesException('Can`t reach this place, needed for delete IDE error highlight');
    }

    /**
     * Level setter
     * @param $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * Recursive method for traverse tree memory structure
     * @param array $children
     * @param $level
     * @return bool
     */
    private function recursiveLevel(array $children, $level)
    {
        foreach ($children as $id => $node)
            if ($this->id === $id) return $level;

        $level++;

        foreach ($children as $node) {
            if (isset($node['children']))
                if ($result = $this->recursiveLevel($node['children'], $level)) return $result;
        }

        return false;
    }

    /**
     * Returns name of node for various lists
     * @param LanguagesDb|null $language
     * @return mixed
     */
    abstract public function getNodeName(LanguagesDb $language = null);

    /**
     * Returns name of field uses for tree nodes sort
     * @return string
     */
    abstract public function getSortFieldName();
}
