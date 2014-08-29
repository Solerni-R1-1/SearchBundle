<?php

namespace Orange\SearchBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Finder\Finder;

/**
 * Description of FilterFactory
 *
 * @author aameziane
 * 
 * @DI\Service("orange.search.filter_manager")
 */
class FilterManager
{
    
    const FILTER_PATTERN   = 'Filter*.php';
    
    private $filterClassNameMap;
    
    /**
     * @DI\InjectParams({
     *     "translator"         = @DI\Inject("translator"),
     *     "kernel"             = @DI\Inject("kernel")
     * })
     */
    public function __construct(
    Translator $translator, KernelInterface $kernel
    )
    {
        $this->translator = $translator;
        $this->kernel = $kernel;
        $this->filterClassNameMap = $this->createFilterClassNameMap();
    }

    public function getFilterClassNameMap()
    {
        return $this->filterClassNameMap;
    }

    public function getFilterClassName($name)
    {

        foreach ($this->getFilterClassNameMap() as $filter) {
            if ($filter['name'] == $name) {
                return $filter['class_name'];
            }
        }
    }
    
    public function getFilterClassNameByShortCut($name)
    {

        foreach ($this->getFilterClassNameMap() as $filter) {
            if ($filter['shortcut'] == $name) {
                return $filter['class_name'];
            }
        }
    }

    public static function get($serviceName)
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer()->get($serviceName);
    }

    /**
     * get all Filter classes and
     * 
     * @return array
     */
    public function createFilterClassNameMap()
    {
        $map = array();
        $ds = DIRECTORY_SEPARATOR;

        foreach ($this->get('kernel')->getBundles() as $bundle) {
            $filterDirectory = $bundle->getPath() . $ds . 'SearchFilter';
            if (file_exists($filterDirectory)) {
                $finder = new Finder();
                $fileIterator = $finder->files()
                                   ->name(self::FILTER_PATTERN)
                                   ->in($filterDirectory);

                foreach ($fileIterator as $file) {
                    $fp = fopen($file->getRealpath(), 'r');
                    $class = $namespace = $buffer = '';
                    $i = 0;
                    while (!$class) {
                        if (feof($fp))
                            break;

                        $buffer .= fread($fp, 512);
                        $tokens = token_get_all($buffer);

                        if (strpos($buffer, '{') === false)
                            continue;

                        for (; $i < count($tokens); $i++) {

                            if ($tokens[$i][0] === T_NAMESPACE) {
                                for ($j = $i + 1; $j < count($tokens); $j++) {
                                    if ($tokens[$j][0] === T_STRING) {
                                        $namespace .= '\\' . $tokens[$j][1];
                                    } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                                        break;
                                    }
                                }
                            }

                            if ($tokens[$i][0] === T_ABSTRACT) {
                                break;
                            }

                            if ($tokens[$i][0] === T_CLASS) {
                                for ($j = $i + 1; $j < count($tokens); $j++) {
                                    if ($tokens[$j] === '{') {
                                        $class = $tokens[$i + 2][1];
                                    }
                                }
                            }
                        }
                    }
                    if ($namespace && $class) {
                        $className = $namespace . '\\' . $class;

                        if ( in_array('Orange\SearchBundle\SearchFilter\InterfaceFilter', class_implements($className)) &&
                            !in_array($className, $map)) {
                            $map [] = array(
                                'path'  => $file->getRealpath(),
                                'class_name' => $className,
                                'name' => $className::getName(),
                                'shortcut' => $className::getShortCut()
                            );
                        }
                    }
                }
            }
        }
        return $map;
    }

}
