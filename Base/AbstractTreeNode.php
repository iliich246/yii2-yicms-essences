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
    private $collection = null;
    /** @var integer level of node in tree structure  */
    private $level = null;

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
     * Sets collection that keep this node
     * @param AbstractTreeNodeCollection $collection
     */
    public function setCollection(AbstractTreeNodeCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Returns name of node for various list
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
