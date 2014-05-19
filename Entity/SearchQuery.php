<?php

namespace Orange\SearchBundle\Entity;

/**
 * Description of Request
 *
 * @author aameziane
 */
class SearchQuery
{

    private $fullText;

    public function getFullText()
    {
        return $this->fullText;
    }

    public function setFullText($fullText)
    {
        $this->fullText = $fullText;
    }

}
