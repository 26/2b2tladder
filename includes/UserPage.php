<?php

require 'Renderer.php';

class UserPage
{
    /**
     * @var HtmlRenderer
     */
    private $html_renderer;

    /**
     * @var string
     */
    private $username;

    public function loadUserPage($username) {
        if(!is_string($username)) {
            throw new InvalidArgumentException("Username must be of type string.");
        }

        $this->username = $username;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function render() {
        // TODO

        $this->html_renderer = new HtmlRenderer();

        $this->html_renderer->outputPage(
            "2b2t Ladder • " . $this->username,
            $this->html_renderer->renderHeader()
        );

        $player = new Renderer($this->username, '12', '12', '12', '12', '12', '21', '12', 'true', 'false', 'base64', '12', 'true', 'true');
        $png = $player->get3DRender();

        echo "<img src='data:image/png;base64,$png' alt='' />";
    }
}