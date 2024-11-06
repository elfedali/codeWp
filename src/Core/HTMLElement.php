<?php

namespace CodeWp\Core;

/**
 * Class HTMLElement
 * @package CodeWp\Core
 * @property string $tag
 * @property array $attributes
 * @property array $content
 * @method void setAttribute(string $name, string $value)
 * @method void addContent(string $content)
 * 
 */
class HTMLElement
{
    public  $tag;
    public  $attributes = [];
    public $content = [];

    public function __construct($tag)
    {
        $this->tag = $tag;
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function addContent($content)
    {
        $this->content[] = $content;
    }

    public function __toString()
    {
        $html = "<{$this->tag}";
        foreach ($this->attributes as $name => $value) {
            $html .= " {$name}=\"{$value}\"";
        }
        $html .= ">";
        foreach ($this->content as $content) {
            $html .= $content;
        }
        $html .= "</{$this->tag}>";
        return $html;
    }
}
