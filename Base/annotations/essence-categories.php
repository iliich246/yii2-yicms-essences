<?php

/** @var $this yii\web\View */
/** @var $annotator \Iliich246\YicmsCommon\Annotations\Annotator */
/** @var $essenceCategory \Iliich246\YicmsEssences\Base\EssencesCategories */

$essenceCategory = $annotator->getAnnotatorFileObject();
echo "<?php\n";
?>

namespace <?= $annotator->getNamespace() ?>;

use Yii;
use Iliich246\YicmsCommon\Annotations\AnnotatorFileInterface;
use <?= $annotator->getExtendsUseClass() ?>;
use <?= $essenceCategory->representClassName() ?>;

/**
 * Class <?= $annotator->getClassName() ?>

 *
 * This class was generated automatically
 *
 * |||-> This part of annotation will be change automatically. Do not change it.
 *
 * |||<- End of block of auto annotation
 *
 * @method <?= $essenceCategory->getEssence()->getAnnotationFileName() ?>Represent[] getRepresents()
 *
 * @property <?= $essenceCategory->getEssence()->getAnnotationFileName() ?>Represent[] $represents
 *
 * @author iliich246 <iliich246@gmail.com>
 */
class <?= $annotator->getClassName() ?> extends <?= $annotator->getExtendsClassName() ?>

{
    /** @var AnnotatorFileInterface instance */
    protected static $parentFileAnnotator;
}
