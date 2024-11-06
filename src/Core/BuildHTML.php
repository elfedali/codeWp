<?php

namespace CodeWp\Core;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BuildHTML
 * 
 * This class is responsible for reading a YAML file and generating HTML content
 * based on the structure defined within the YAML file. It supports adding CDN links,
 * inline and external scripts, and content sections with nested elements.
 *
 * @package CodeWp\Core
 */
class BuildHTML
{
    /**
     * @var array Holds parsed YAML data
     */
    private array $yamlData;

    /**
     * @var string Accumulates the generated HTML output
     */
    private string $htmlOutput = '';

    /**
     * BuildHTML constructor.
     *
     * Initializes the class by loading and parsing the YAML file.
     *
     * @param string $yamlFilePath The path to the YAML file
     */
    public function __construct(string $yamlFilePath)
    {
        $this->loadYaml($yamlFilePath);
    }

    /**
     * Loads and parses a YAML file into the yamlData array.
     *
     * @param string $filePath The path to the YAML file
     * @throws \Exception if YAML parsing fails
     */
    private function loadYaml(string $filePath): void
    {
        try {
            $this->yamlData = Yaml::parseFile($filePath);
        } catch (ParseException $exception) {
            throw new \Exception("YAML Parsing Error: " . $exception->getMessage());
        }
    }

    /**
     * Generates HTML tags for CDN links from the parsed YAML data.
     *
     * Adds `<link>` tags for CSS and `<script>` tags for JavaScript.
     */
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

    /**
     * Generates HTML for inline and external scripts defined in the YAML data.
     *
     * Adds `<script>` tags with either `src` attributes for external files or inline JavaScript code.
     */
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

    /**
     * Recursively generates HTML for content sections and nested elements.
     *
     * Iterates through each element in the content array and builds the HTML structure
     * by setting tag attributes, nested content, and closing tags.
     *
     * @param array $contentArray The array of content elements to generate HTML for
     * @return string Generated HTML for the content section
     */
    private function generateContent(array $contentArray): string
    {
        $html = '';
        foreach ($contentArray as $content) {
            $tag = $content['tag'];
            $attributes = '';

            // Build element attributes from YAML key-value pairs
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

            // Open the tag with attributes
            $html .= "<$tag$attributes>";

            // Add nested content or text content
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

    /**
     * Builds the complete HTML structure based on the YAML configuration.
     *
     * This method orchestrates the process by generating CDN links, content sections, 
     * and scripts in sequence and returning the final HTML output.
     *
     * @return string The complete HTML output
     * @throws \Exception if YAML loading or parsing encounters errors
     */
    public function build(): string
    {
        $this->generateCdns();

        if (!empty($this->yamlData['sections'])) {
            $this->htmlOutput .= $this->generateContent($this->yamlData['sections']);
        }

        $this->generateScripts();

        return $this->htmlOutput;
    }
}
