<?php


namespace Api;

class ApiOutput
{
    const DEFAULT_OUTPUT_MODE = "json";
    const VALID_OUTPUT_MODES = [
        "json",
        "xml"
    ];

    /**
     * @var string
     */
    public $output_mode;

    /**
     * @var \HtmlRenderer
     */
    private $html_renderer;

    /**
     * ApiOutput constructor.
     */
    public function __construct()
    {
        $this->setOutputMode(self::DEFAULT_OUTPUT_MODE);

        $this->html_renderer = new \HtmlRenderer();
    }

    /**
     * @param $output_mode
     */
    public function setOutputMode($output_mode) {
        if(self::isValidOutputMode($output_mode)) {
            $this->output_mode = $output_mode;
        }
    }

    /**
     * @param array $result
     * @throws \Exception
     */
    public function outputResult(array $result) {
        switch($this->output_mode) {
            case "json":
                $out = json_encode($result);
                break;
            case "xml":
                $out = $this->outputResultAsXml($result);
                break;
            default:
                throw new \InvalidArgumentException("Output mode is invalid.");
        }

        $this->html_renderer->outputPage(
            "2b2tladder API result",
            $this->html_renderer->unsafeRenderText(
                (string)$out
            )
        );

        exit();
    }

    /**
     * @param $output_mode
     * @return bool
     */
    public static function isValidOutputMode($output_mode) {
        return in_array($output_mode, self::VALID_OUTPUT_MODES);
    }

    /**
     * @param array $result
     * @return mixed
     * @throws \Exception
     */
    private function outputResultAsXml(array $result) {
        $xml = new \SimpleXMLElement('<root/>');
        array_walk_recursive(array_flip($result), array($xml, 'addChild'));

        return $xml->asXML();
    }
}