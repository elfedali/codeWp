<?php

namespace CodeWp\Core;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BuildHTML
 * @package CodeWp\Core
 * 
 */
class BuildHTML
{
    private array $yamlData;
    private string $htmlOutput = '';

    public function __construct(string $yamlFilePath)
    {
        $this->loadYaml($yamlFilePath);
    }

    // Load and parse YAML file
    private function loadYaml(string $filePath): void
    {
        try {
            $this->yamlData = Yaml::parseFile($filePath);
        } catch (ParseException $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    // Generate HTML for CDN links
    private function generateCdns(): void
    {
        if (!empty($this->yamlData['cdns'])) {
            foreach ($this->yamlData['cdns'] as $cdn) {
                $ext = pathinfo($cdn, PATHINFO_EXTENSION);
                if ($ext === 'css') {
                    $this->htmlOutput .= "<link rel='stylesheet' href='{$cdn}'>\n";
                } elseif ($ext === 'js') {
                    $this->htmlOutput .= "<script src='{$cdn}'></script>\n";
                }
            }
        }
    }

    // Generate HTML for scripts
    private function generateScripts(): void
    {
        if (!empty($this->yamlData['scripts'])) {
            foreach ($this->yamlData['scripts'] as $script) {
                if (isset($script['src'])) {
                    $this->htmlOutput .= "<script src='{$script['src']}'></script>\n";
                } elseif (isset($script['inline'])) {
                    $this->htmlOutput .= "<script>\n{$script['inline']}\n</script>\n";
                }
            }
        }
    }

    // Recursively generate HTML for content sections
    private function generateContent(array $contentArray): string
    {
        $html = '';
        foreach ($contentArray as $content) {
            $tag = $content['tag'];
            $attributes = '';

            // Set element attributes
            foreach ($content as $attr => $value) {
                if ($attr !== 'tag' && $attr !== 'content') {
                    if (is_array($value)) {
                        $attributes .= " $attr='";
                        foreach ($value as $key => $val) {
                            $attributes .= "$key:$val;";
                        }
                        $attributes .= "'";
                    } else {
                        $attributes .= " $attr=\"$value\"";
                    }
                }
            }

            // Open the tag
            $html .= "<$tag $attributes>";

            // Process nested content or plain content
            if (isset($content['content']) && is_array($content['content'])) {
                $html .= $this->generateContent($content['content']);
            } elseif (isset($content['content'])) {
                $html .= $content['content'];
            }

            // Close the tag
            $html .= "</$tag>";
        }
        return $html;
    }

    // Main method to build the entire HTML output
    public function build(): string
    {
        // Generate parts
        $this->generateCdns();



        if (!empty($this->yamlData['sections'])) {
            $this->htmlOutput .= $this->generateContent($this->yamlData['sections']);
        }
        $this->generateScripts();

        return $this->htmlOutput;
    }
}
