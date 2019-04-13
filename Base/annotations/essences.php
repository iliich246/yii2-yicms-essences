<?php

/** @var $this yii\web\View */
/** @var $annotator \Iliich246\YicmsCommon\Annotations\Annotator */
/** @var $essenceInstance \Iliich246\YicmsEssences\Base\Essences */

$essenceInstance = $annotator->getAnnotatorFileObject();
echo "<?php\n";
?>

namespace <?= $annotator->getNamespace() ?>;

use Yii;
use <?= $annotator->getExtendsUseClass() ?>;
use <?= $essenceInstance->categoryClassName() ?>;
use <?= $essenceInstance->representClassName() ?>;

/**
 * Class <?= $annotator->getClassName() ?>

 *
 * This class was generated automatically
 *
 * |||-> This part of annotation will be change automatically. Do not change it.
 *
 * |||<- End of block of auto annotation
 *
 * @method <?= $essenceInstance->getAnnotationFileName() ?>Category getCategoryById($id)
 * @method <?= $essenceInstance->getAnnotationFileName() ?>Represent getRepresentById($id)
 * @method <?= $essenceInstance->getAnnotationFileName() ?>Represent[] getAllRepresents()
 * @method <?= $essenceInstance->getAnnotationFileName() ?>Category[] getCategories()
 * @method <?= $essenceInstance->getAnnotationFileName() ?>Category[] getTopCategories()
 *
 * @property <?= $essenceInstance->getAnnotationFileName() ?>Category[] $categories
 * @property <?= $essenceInstance->getAnnotationFileName() ?>Category[] $topCategories
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class <?= $annotator->getClassName() ?> extends <?= $annotator->getExtendsClassName() ?>

{
    /**
    * @return self instance .
    * @throws \Iliich246\YicmsEssences\Base\EssencesException
    */
    public static function getInstance()
    {
        return self::getByName('<?= $essenceInstance->program_name ?>');
    }
}

