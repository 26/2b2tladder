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

    const CSS_FOLDER = '/css/';
    const IMAGE_FOLDER = '/images/';
    const JAVASCRIPT_FOLDER = '/js/';

    const STYLESHEET = self::CSS_FOLDER . 'style.css';
    const LOGO_MAIN = self::IMAGE_FOLDER . 'logo.png';

    const DEFAULT_SKIN_URL = ''; // TODO
    const DEFAULT_DOCTYPE = 'HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"';

    /**
     * @throws Exception
     */
    public function outputPage() {
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
            $this->renderEmptyTag(
                'script',
                [
                    'src' => 'https://code.jquery.com/jquery-3.4.1.min.js',
                    'integrity' => 'sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=',
                    'crossorigin' => 'anonymous'
                ]
            ),
            $this->renderTag(
                'meta',
                ['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1']
            ),
            $this->renderTag(
                'meta',
                ['name' => 'description', 'content' => 'Track and compare your statistics on 2b2t.']
            ),
            $this->renderTag(
                'meta',
                ['name' => 'author', 'content' => '2b2tladder']
            ),
            $this->renderTag(
                'meta',
                ['name' => 'keywords', 'content' => '2b2t leaderboard,2b2tladder,2b2tleaderboard,2b2t statistics,2b2t api']
            ),
            $this->renderTag(
                'meta',
                ['name' => 'copyright', 'content' => '2b2tladder']
            ),
            $this->renderTag(
                'meta',
                ['name' => 'url', 'content' => 'https://2b2tladder.com']
            ),
            $this->renderTag(
                'meta',
                ['name' => 'apple-mobile-web-app-title', 'content' => '2b2tladder']
            ),
            $this->renderTag(
                'meta',
                ['name' => 'application-name', 'content' => '2b2tladder']
            ),
            $this->renderTag(
                'meta',
                ['name' => 'mobile-web-app-capable', 'content' => 'yes']
            ),
            $this->renderTag(
                'meta',
                ['name' => 'robots', 'content' => 'index,nofollow']
            ),
            $this->renderTag(
                'meta',
                ['name' => 'revisit-after', 'content' => '1 month']
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
                'link',
                ['href' => '/lib/fontawesome/css/all.css', 'rel' => 'stylesheet']
            ),
            $this->renderEmptyTag(
                'script',
                ['src' => self::JAVASCRIPT_FOLDER . 'page.js', 'type' => 'text/javascript']
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
     * @param $skin_base64
     * @return Tag
     * @throws Exception
     */
    public function renderSkin($skin_base64) {
        if(!base64_decode($skin_base64)) {
            $skin = self::DEFAULT_SKIN_URL;
        } else {
            $skin = "data:image/png;base64," . $skin_base64;
        }

        return $this->renderTag(
            'img',
            [
                'class' => 'skin-image',
                'src' => $skin,
                'alt' => ''
            ]
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
     * Renders the arguments inside the wrapper class.
     *
     * @return Tag
     * @throws Exception
     */
    public function renderWrapper()
    {
        $arguments = func_get_args();

        return $this->renderTag(
            'div',
            ['class' => 'wrapper'],
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

    /**
     * Renders the home page search.
     * @throws Exception
     */
    public function renderHomePageSearch()
    {
        return $this->renderTag(
            'div',
            ['class' => 'search-main'],
            $this->renderTag(
                'div',
                ['class' => 'search-form-wrapper'],
                $this->renderTag(
                    'h1',
                    [],
                    $this->renderText("Search user")
                ),
                $this->renderForm(
                    [
                        'class'  => 'search-form',
                        'method' => 'POST',
                        'action' => '/search'
                    ],
                    $this->renderInput(
                        'text',
                        [
                            'class' => 'search-box',
                            'id' => 'search-box',
                            'placeholder' => 'Search user',
                            'name' => 'search'
                        ]
                    )
                ),
                $this->renderTag(
                    'div',
                    ['class' => 'return-icon'],
                    $this->renderTag(
                        'img',
                        ['src' => self::IMAGE_FOLDER . 'icons8-enter-key-30.png', 'alt' => '']
                    )
                ),
                $this->renderTag(
                    'div',
                    ['class' => 'search-suggestions'],
                    $this->renderTag(
                        'p',
                        [],
                        $this->renderText("You can search for:")
                    ),
                    $this->renderTag(
                        'ul',
                        [],
                        $this->renderTag(
                            'li',
                            [],
                            $this->renderText(
                                'Minecraft username'
                            )
                        ),
                        $this->renderTag(
                            'li',
                            [],
                            $this->renderText(
                                'Minecraft UUID'
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderFooter() {
        return $this->renderTag(
            'div',
            ['class' => 'footer'],
            $this->renderTag(
                'div',
                ['class' => 'footer-image'],
                $this->renderTag(
                    'img',
                    ['src' => self::LOGO_MAIN, 'alt' => '']
                )
            ),
            $this->renderTag(
                'div',
                ['class' => 'footer-text'],
                $this->renderTag(
                    'p',
                    [],
                    $this->renderText(
                        'This site is not affiliated with 2b2t.'
                    )
                ),
                $this->renderTag(
                    'p',
                    [],
                    $this->renderText(
                        'Icons by Icons8 - Data from '
                    ),
                    $this->renderTag(
                        'a',
                        ['href' => 'http://2b2t.dev', 'class' => 'footer-link'],
                        $this->renderText(
                            'api.2b2t.dev'
                        )
                    ),
                    $this->renderText(
                        ' - Background image by '
                    ),
                    $this->renderTag(
                        'a',
                        ['href' => 'https://www.reddit.com/r/2b2t/comments/dbmpda/a_highquality_picture_of_the_bedrock_comet_ziggy/', 'class' => 'footer-link'],
                        $this->renderText(
                            'u/Raviable'
                        )
                    )
                ),
                $this->renderTag(
                    'p',
                    [],
                    $this->renderText(
                        '© 2020 - '
                    ),
                    $this->renderTag(
                        'a',
                        ['href' => 'https://github.com/Pancake/2b2tladder', 'class' => 'footer-link'],
                        $this->renderText(
                            'GitHub'
                        )
                    )
                )
            )
        );
    }

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderForm()
    {
        $arguments = func_get_args();
        $attributes = array_shift($arguments);

        return $this->renderTag(
            'form',
            $attributes,
            ...$arguments
        );
    }

    /**
     * @param $bool
     * @return Tag
     * @throws Exception
     */
    public function renderBooleanIcon($bool) {
        if($bool) {
            return $this->renderTag(
                'span',
                ['class' => 'boolean-icon true fas fa-check']
            );
        } else {
            return $this->renderTag(
                'span',
                ['class' => 'boolean-icon false fas fa-times']
            );
        }
    }

    /**
     * @param $string
     * @return Tag
     * @throws Exception
     */
    public function renderInlineError($string) {
        return $this->renderTag(
            'span',
            ['class' => 'error'],
            $this->renderText(
                $string
            )
        );
    }

    /**
     * @param $string
     * @return Tag
     * @throws Exception
     */
    public function renderError($string) {
        return $this->renderTag(
            'div',
            ['class' => 'error'],
            $this->renderTag(
                'p',
                [],
                $this->renderText(
                    $string
                )
            )
        );
    }

    /**
     * Renders an input tag.
     *
     * @param $input_type
     * @param array $attributes
     * @return Tag
     * @throws Exception
     */
    public function renderInput($input_type, array $attributes)
    {
        return $this->renderTag(
            'input',
            array_merge(['type' => $input_type], $attributes)
        );
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
     * @return Tag
     * @throws Exception
     */
    public function renderText($text)
    {
        if(!is_string($text)) {
            throw new InvalidArgumentException();
        }

        return (new Tag())->setTag(htmlspecialchars($text));
    }

    /**
     * @param $tag_name
     * @param array $attributes
     * @return Tag
     * @throws Exception
     */
    public function renderEmptyTag($tag_name, array $attributes) {
        if(!is_string($tag_name) || !ctype_alnum($tag_name) || !is_array($attributes)) {
            throw new InvalidArgumentException();
        }

        $tag = '<' . $tag_name;

        foreach($attributes as $attribute_name => $attribute) {
            $tag .= ' ' . htmlspecialchars($attribute_name) . '="' . htmlspecialchars($attribute) . '"';
        }

        $tag .= '></' . $tag_name . '>';

        return (new Tag())->setTag($tag);
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
     * @param null $active
     * @return Tag
     * @throws Exception
     */
    public function renderHeader($active = null)
    {
        return $this->renderTag( # Main header tag
            "div",
            [
                "class" => "header"
            ],
            $this->renderTag( # Header inner-wrapper
                "div",
                ["class" => "header-inner-wrapper"],
                $this->renderTag(
                    'a',
                    ['href' => '/'],
                    $this->renderTag( # Logo
                        "img",
                        [
                            "src" => self::LOGO_MAIN,
                            "class" => "logo-header",
                            "alt" => ""
                        ]
                    )
                ),
                $this->renderTag( # Menu
                    "ul",
                    [
                        "class" => "menu"
                    ],
                    ...$this->renderMenuLinks($active)
                )
            )
        );
    }

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderErrorPage() {
        $arguments = func_get_args();

        return $this->renderTag(
            'div',
            ['class' => 'container error-page'],
            ...$arguments
        );
    }

    /**
     * @param $string
     * @param $code
     * @return Tag
     * @throws Exception
     */
    public function renderErrorMessage($string, $code) {
        return $this->renderTag(
            'h1',
            ['class' => 'error-title'],
            $this->renderText(
                "Error $code: $string"
            )
        );
    }

    /**
     * @param $description
     * @return Tag
     * @throws Exception
     */
    public function renderErrorDescription($description) {
        if(!$description) return $this->renderTag(
            'p',
            ['class' => 'error-description'],
            $this->renderText(
                ':~('
            )
        );

        return $this->renderTag(
            'p',
            ['class' => 'error-description'],
            $this->renderText(
                $description
            ),
            $this->renderTag(
                'br',
                []
            ),
            $this->renderTag(
                'br',
                []
            ),
            $this->renderText(
                ':~('
            )
        );
    }

    /**
     * @return Tag
     * @throws Exception
     */
    public function renderFAQ() {
        return $this->renderTag(
            'div',
            ['class' => 'container faq'],
            $this->renderTag(
                'h1',
                [],
                $this->renderText(
                    "Frequently  Asked Questions"
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-q'],
                $this->renderText(
                    'What is this?'
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-a'],
                $this->renderText(
                    'It is a site for tracking your progess on 2b2t.org.'
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-q'],
                $this->renderText(
                    'Why is the data shown here inaccurate?'
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-a'],
                $this->renderText(
                    'Our data is sourced by a bot. This bot can only source data when it it online, therefore, no data was recorded before the bot existed and no data is recorded while the bot is in queue.'
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-q'],
                $this->renderText(
                    'Is there a Discord server?'
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-a'],
                $this->renderText(
                    'Yes, there is. Click on More > Discord in the header to join it.'
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-q'],
                $this->renderText(
                    'Is there an API available?'
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-a'],
                $this->renderText(
                    'No, not from us, but all our data is sourced from api.2b2t.dev.'
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-q'],
                $this->renderText(
                    'I want to remove my profile from 2b2tladder.'
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-a'],
                $this->renderText(
                    'This is currently impossible.'
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-q'],
                $this->renderText(
                    'I found an issue. Where can I report it?'
                )
            ),
            $this->renderTag(
                'p',
                ['class' => 'faq-a'],
                $this->renderText(
                    'Please join our Discord server and mention @developer.'
                ),
                $this->renderTag(
                    'br',
                    []
                ),
                $this->renderTag(
                    'br',
                    []
                ),
                $this->renderText(
                    'If you have found a security issue, please contact @Marijn#6399 privately. I offer a $5,- reward for any security bug that is responsibly disclosed to me.'
                )
            )
        );
    }

    /**
     * @param null $active
     * @return array
     * @throws Exception
     */
    private function renderMenuLinks($active = null) {
        $links = [];

        $links[] = $this->renderTag(
            "li",
            [
                "class" => "menu-item"
            ],
            $this->renderTag(
                "a",
                [
                    "class" => $this->getMenuLinkClasses("home", $active),
                    "href" => "/"
                ],
                $this->renderText(
                    "Home"
                )
            )
        );

        $links[] = $this->renderTag(
            "li",
            [
                "class" => "menu-item"
            ],
            $this->renderTag(
                "a",
                [
                    "class" => "dropdown " . $this->getMenuLinkClasses("leaderboards", $active),
                    "href" => "#"
                ],
                $this->renderText(
                    "Leaderboards"
                ),
                $this->renderTag(
                    "span",
                    ["class" => "dropdown-icon fas fa-sort-down"]
                )
            ),
            $this->renderTag(
                "div",
                [
                    "class" => "dropdown-menu"
                ],
                $this->renderTag(
                    'ul',
                    [],
                    $this->renderTag(
                        'li',
                        [],
                        $this->renderTag(
                            'a',
                            ['class' => 'dropdown-link', 'href' => '/ladder/kills'],
                            $this->renderText(
                                'Most kills'
                            )
                        )
                    ),
                    $this->renderTag(
                        'li',
                        [],
                        $this->renderTag(
                            'a',
                            ['class' => 'dropdown-link', 'href' => '/ladder/deaths'],
                            $this->renderText(
                                'Most deaths'
                            )
                        )
                    ),
                    $this->renderTag(
                        'li',
                        [],
                        $this->renderTag(
                            'a',
                            ['class' => 'dropdown-link', 'href' => '/ladder/joins'],
                            $this->renderText(
                                'Most joins'
                            )
                        )
                    ),
                    $this->renderTag(
                        'li',
                        [],
                        $this->renderTag(
                            'a',
                            ['class' => 'dropdown-link', 'href' => '/ladder/leaves'],
                            $this->renderText(
                                'Most leaves'
                            )
                        )
                    )
                )
            )
        );

        $links[] = $this->renderTag(
            "li",
            [
                "class" => "menu-item"
            ],
            $this->renderTag(
                "a",
                [
                    "class" => "dropdown menu-link",
                    "href" => "#"
                ],
                $this->renderText(
                    "More"
                ),
                $this->renderTag(
                    "span",
                    ["class" => "dropdown-icon fas fa-sort-down"]
                )
            ),
            $this->renderTag(
                "div",
                [
                    "class" => "dropdown-menu"
                ],
                $this->renderTag(
                    'ul',
                    [],
                    $this->renderTag(
                        'li',
                        [],
                        $this->renderTag(
                            'a',
                            ['class' => 'dropdown-link', 'href' => '/more/faq'],
                            $this->renderText(
                                'FAQ'
                            )
                        )
                    ),
                    $this->renderTag(
                        'li',
                        [],
                        $this->renderTag(
                            'a',
                            ['class' => 'dropdown-link', 'href' => '/more/discord'],
                            $this->renderText(
                                'Discord'
                            )
                        )
                    )
                )
            )
        );

        return $links;
    }

    /**
     * @param Tag $title
     * @param Tag $table
     * @return Tag
     * @throws Exception
     */
    public function renderSearch(Tag $title, Tag $table) {
        return $this->renderTag(
            'div',
            ['class' => 'search container'],
            $this->renderTag(
                'h1',
                [],
                $title
            ),
            $table
        );
    }

    /**
     * @param $link
     * @param null $active
     * @return string
     */
    private function getMenuLinkClasses($link, $active = null)
    {
        if($link === $active) {
            return "menu-link active";
        } else {
            return "menu-link";
        }
    }
}