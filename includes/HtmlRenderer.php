<?php

/**
 * Class HtmlRenderer
 *
 * Handles page output and renders HTML.
 */
class HtmlRenderer
{
    const LANGUAGE = 'en';
    const CHARSET = 'UTF-8';

    const CSS_FOLDER = 'css/';
    const IMAGE_FOLDER = 'images/';

    const STYLESHEET = self::CSS_FOLDER . 'style.css';
    const LOGO_MAIN = self::IMAGE_FOLDER . 'logo.png';

    const DEFAULT_DOCTYPE = 'HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"';

    /**
     * @throws Exception
     */
    public function renderPage() {
        $arguments = func_get_args();

        echo $this->renderDoctype();
        echo $this->renderHtml(
            ...$arguments
        )->tag;
    }

    /**
     * @throws Exception
     */
    public function renderHtml()
    {
        $arguments = func_get_args();
        $page_title = array_shift($arguments);

        return $this->renderTag(
            'html',
            ['lang' => self::LANGUAGE],
            $this->renderHead($page_title),
            $this->renderBody(
                ...$arguments
            )
        );
    }

    /**
     * @param $page_title
     * @return string
     * @throws Exception
     */
    public function renderHead($page_title)
    {
        if(!is_string($page_title)) {
            throw new InvalidArgumentException("Title must of a string.");
        }

        return $this->renderTag(
            'head',
            [],
            $this->renderTag(
                'meta',
                ['charset' => self::CHARSET]
            ),
            $this->renderTag(
                'link',
                ['rel' => 'stylesheet', 'href' => self::STYLESHEET]
            ),
            $this->renderTag(
                'link',
                ['href' => 'https://fonts.googleapis.com/css?family=Open+Sans&display=swap', 'rel' => 'stylesheet']
            ),
            $this->renderTag(
                'title',
                [],
                $this->renderText(
                    $page_title
                )
            )
        );
    }

    /**
     * Renders the body.
     *
     * @return string
     * @throws Exception
     */
    public function renderBody()
    {
        $arguments = func_get_args();

        return $this->renderTag(
            'body',
            ['lang' => self::LANGUAGE],
            ...$arguments
        );
    }

    /**
     * Renders the home page.
     *
     * @return Tag
     * @throws Exception
     */
    public function renderHomePage()
    {
        $arguments = func_get_args();

        return $this->renderTag(
            'div',
            ['class' => 'homepage'],
            ...$arguments
        );
    }

    public function renderHomePageSearch()
    {

    }

    /**
     * Renders the DOCTYPE attribute.
     */
    public function renderDoctype()
    {
        return '<!DOCTYPE ' . self::DEFAULT_DOCTYPE . '>';
    }

    /**
     * Safely renders some text.
     *
     * @param $text
     * @return string
     */
    public function renderText($text)
    {
        if(!is_string($text)) {
            throw new InvalidArgumentException();
        }

        return (new Tag())->setTag(htmlspecialchars($text));
    }

    /**
     * Safely renders an HTML tag.
     *
     * @return Tag
     * @throws Exception
     */
    public function renderTag()
    {
        $arguments = func_get_args();
        $tag_name = array_shift($arguments);
        $attributes = array_shift($arguments);

        if(!is_string($tag_name) || !ctype_alnum($tag_name) || !is_array($attributes)) {
            throw new InvalidArgumentException();
        }

        $tag = '<' . $tag_name;

        foreach($attributes as $attribute_name => $attribute) {
            $tag .= ' ' . htmlspecialchars($attribute_name) . '="' . htmlspecialchars($attribute) . '"';
        }

        if(count($arguments) > 0) {
            $tag .= '>';

            foreach($arguments as $inner_tag) {
                if(get_class($inner_tag) !== "Tag") {
                    throw new InvalidArgumentException();
                }

                $tag .= $inner_tag->tag;
            }

            $tag .= '</' . $tag_name . '>';
        } else {
            $tag .= '/>';
        }

        return (new Tag())->setTag($tag);
    }

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderHeader()
    {
        return $this->renderTag( # Main header tag
            "div",
            [
                "class" => "header"
            ],
            $this->renderTag( # Header inner-wrapper
                "div",
                ["class" => "header-inner-wrapper"],
                $this->renderTag( # Logo
                    "img",
                    [
                        "src" => self::LOGO_MAIN,
                        "class" => "logo-header",
                        "alt" => ""
                    ]
                ),
                $this->renderTag( # Menu
                    "ul",
                    [
                        "class" => "menu"
                    ],
                    $this->renderTag(
                        "li",
                        [
                            "class" => "menu-item"
                        ],
                        $this->renderTag(
                            "a",
                            [
                                "class" => "menu-link",
                                "href" => "home"
                            ],
                            $this->renderText(
                                "Home"
                            )
                        )
                    )
                )
            )
        );
    }
}