<?php

/**
 * Class Tag
 */
final class Tag extends HtmlRenderer
{
    /**
     * @var string
     */
    protected $tag = '';

    /**
     * @param $tag
     * @return $this
     */
    protected function setTag($tag) {
        if(!is_string($tag)) {
            throw new InvalidArgumentException('Tag must be of type string.');
        }

        $this->tag = $tag;
        return $this;
    }
}