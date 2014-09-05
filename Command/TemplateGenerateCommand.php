<?php

namespace Orange\SearchBundle\Command;



use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Claroline\BundleRecorder\Detector\Detector;
use Symfony\Component\Finder\Finder;

/**
 * Description of tempalteGenerateCommand
 *
 * @author aameziane
 */
class TemplateGenerateCommand extends ContainerAwareCommand
{

    
    protected function configure()
    {
        parent::configure();
        $this->setName('claroline:search:template:symlink')
             ->setDescription('Look for entites templates into all activated bundle, and move to search ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $ds = DIRECTORY_SEPARATOR;
        $kernel = $this->getContainer()->get('kernel');
        $vendorDirectory = $kernel->getRootDir() . $ds.'..'.$ds.'vendor';
        
        // generate link for template
        $output->writeln('<comment>Generate symlinks for search result template</comment>');
        $searchTemplateDirectoryRealPath = getFirst(
                Finder::create()->directories()
                                ->path('SearchBundle'.$ds.
                                       'Resources'.$ds.
                                       'public'.$ds.
                                       'js'.$ds.              
                                       'ng-search'.$ds.
                                       'search-results'.$ds.
                                       'templates')
                                ->in($vendorDirectory)
        );
        
        
        if ($searchTemplateDirectoryRealPath) {
            $fileIterator = Finder::create()->files()
                   ->path('search-results')
                   ->name('*.html')
                   ->notPath('SearchBundle')
                   ->in($vendorDirectory);
            
            foreach ($fileIterator as $file) {
                $link = $searchTemplateDirectoryRealPath .$ds. basename($file->getRealpath());
                if (!file_exists($link)) {
                    symlink ( $file->getRealpath() , $link );
                    $output->writeln($file->getRealpath() .'<comment> --> </comment>'. $link);
                }
            }
            

            
        } else {
            $output->writeln("Search template directory not found");
        }
        
        // generate link for template
        $output->writeln('<comment>Generate symlinks for search result css</comment>');
        $searchCssDirectoryRealPath = getFirst(
                Finder::create()->directories()
                                ->path('SearchBundle'.$ds.
                                       'Resources'.$ds.
                                       'public'.$ds.
                                       'css')
                                ->in($vendorDirectory)
        );
        if ($searchCssDirectoryRealPath) {
            $fileIterator = Finder::create()->files()
                   ->path('search-results')
                   ->name('*.css')
                   ->notPath('SearchBundle')
                   ->in($vendorDirectory);
            
            foreach ($fileIterator as $file) {
                $link = $searchCssDirectoryRealPath .$ds. basename($file->getRealpath());
                if (!file_exists($link)) {
                    symlink ( $file->getRealpath() , $link );
                    $output->writeln($file->getRealpath() .'<comment> --> </comment>'. $link);
                }
            }
        } else {
            $output->writeln("Search css directory not found");
        }

    }
}

function getFirst($finder)
{
    if ($finder->count() > 0) {
            $finderDirectoriesArray = iterator_to_array($finder->getIterator(), true);
            $director = array_shift($finderDirectoriesArray);
            return $director->getRealpath();
    } 
    return;
}