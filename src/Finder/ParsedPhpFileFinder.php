<?php

namespace SensioLabs\DeprecationDetector\Finder;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\Parser\ParserInterface;
use Symfony\Component\Finder\Finder;

class ParsedPhpFileFinder extends Finder
{
    /**
     * @var ParserInterface
     */
    protected $parser;

    public function __construct()
    {
        parent::__construct();

        $this->files()->name('*.php');
    }

    /**
     * @param ParserInterface $parser
     */
    public function setParser(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return \Iterator
     */
    public function getIterator()
    {
        $iterator = parent::getIterator();

        $files = new \ArrayIterator();
        foreach ($iterator as $file) {
            $file = PhpFileInfo::create($file);

            if (null !== $this->parser) {
                try {
                    $this->parser->parseFile($file);
                } catch (\PhpParser\Error $ex) {
                    $raw = $ex->getRawMessage().' in file '.$ex->getFile();
                    $ex->setRawMessage($raw);

                    throw $ex;
                }
            }

            $files->append($file);
        }

        return $files;
    }

    /**
     * @return int
     */
    public function count()
    {
        return iterator_count(parent::getIterator());
    }
}
