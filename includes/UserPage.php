<?php

class UserPage
{
    /**
     * @var HtmlRenderer
     */
    private $html_renderer;

    /**
     * @var SkinRenderHandler
     */
    private $skin_renderer;

    /**
     * @var DatabaseHandler
     */
    private $database;

    /**
     * @var CacheHandler
     */
    private $cache_handler;

    /**
     * @var IOHandler
     */
    private $io_handler;

    /**
     * @var bool
     */
    private $user_exists = false;

    /**
     * @var string
     */
    private $username;

    /**
     * @var ApiResult
     */
    private $user_result;

    /**
     * @var ApiResult
     */
    private $last_kill_result;

    /**
     * @var ApiResult
     */
    private $last_death_result;

    /**
     * @var string
     */
    private $skin_render;

    /**
     * @var bool
     */
    private $legacy_username = false;

    /**
     * @var bool|string
     */
    private $skin_icon_base64;

    /**
     * @var RankHandler
     */
    private $rank_handler;

    /**
     * UserPage constructor.
     */
    public function __construct() {
        $this->cache_handler = new CacheHandler();
        $this->database = DatabaseHandler::newFromConfig();
        $this->skin_renderer = new SkinRenderHandler($this->database);
        $this->html_renderer = new HtmlRenderer();
        $this->io_handler = new IOHandler();
        $this->rank_handler = new RankHandler();
    }

    /**
     * @param $username
     * @return $this
     * @throws Exception
     */
    public function loadUserPage($username) {
        if(!is_string($username)) {
            throw new InvalidArgumentException("Username must be of type string.");
        }

        if(!$this->loadExternalData($username)) {
            return $this;
        }

        $this->username = $username;
        $this->skin_render = $this->skin_renderer->getSkin($this->username);

        // Refresh skin cache if necessary.
        if(!$this->cache_handler->isSkinCached($this->user_result->getResult()->getUUID()) || $this->cache_handler->skinCachedFor($this->user_result->getResult()->getUUID()) > CacheHandler::CACHE_INVALIDATION_TIME_LIMIT * 4) {
            $this->skin_icon_base64 = $this->io_handler->getSkinAsBase64($this->user_result->getResult()->getUUID());

            $this->cache_handler->clearCacheSkin($this->user_result->getResult()->getUUID());
            $this->cache_handler->cacheSkin($this->user_result->getResult()->getUUID(), $this->skin_icon_base64);
        } else {
            $this->skin_icon_base64 = $this->cache_handler->getCachedSkin($this->user_result->getResult()->getUUID())['skin'];
        }

        $this->rank_handler->loadRanksFrom($this->user_result->getResult());

        $this->user_exists = true;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function render() {
        if($this->legacy_username) {
            // There is currently an issue with processing usernames containing a '_'.
            OutputPage::renderError(
                500,
                "Unsupported username",
                "The external API we are using to fetch our data currently does not support usernames with an underscore in them. This should get resolved soon."
            );
        }
        if(!$this->user_exists) {
            // Page was not loaded or user does not exist.
            OutputPage::renderError(
                404,
                "User not found",
                "The user you are looking for was not found. This could mean you misspelled their name, they haven't joined in a while or their username changed."
            );
        }

        $this->html_renderer->outputPage(
            "2b2t Ladder • $this->username",
            $this->html_renderer->renderHeader(),
            $this->html_renderer->renderWrapper(
                $this->html_renderer->renderTag(
                    'div',
                    ['class' => 'user-page container'],
                    $this->html_renderer->renderTag(
                        'div',
                        ['class' => 'user-page-header'],
                        $this->html_renderer->renderTag(
                            'div',
                            ['class' => 'skin-container'],
                            $this->html_renderer->renderTag(
                                'img',
                                [
                                    'class' => 'skin-render',
                                    'src' => 'data:image/png;base64,' . $this->skin_render,
                                    'alt' => ''
                                ]
                            )
                        ),
                        $this->html_renderer->renderTag(
                            'div',
                            ['class' => 'user-page-title'],
                            $this->html_renderer->renderTag(
                                'h1',
                                [],
                                $this->html_renderer->renderText(
                                    $this->user_result->getResult()->getUsername()
                                )
                            )
                        )
                    ),
                    $this->html_renderer->renderEmptyTag(
                        'div',
                        ['class' => 'line']
                    ),
                    $this->html_renderer->renderTag(
                        'div',
                        ['class' => 'user-page-content'],
                        $this->html_renderer->renderTag(
                            'div',
                            ['class' => 'row'],
                            $this->html_renderer->renderTag(
                                'div',
                                ['class' => 'col-md box'],
                                $this->html_renderer->renderTag(
                                    'h1',
                                    [],
                                    $this->html_renderer->renderText(
                                        'Profile Info'
                                    )
                                ),
                                $this->html_renderer->renderTag(
                                    'table',
                                    [],
                                    $this->html_renderer->renderTag(
                                        'tbody',
                                        [],
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'Username'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    $this->user_result->getResult()->getUsername()
                                                )
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'UUID'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['title' => $this->user_result->getResult()->getUUID()],
                                                $this->html_renderer->renderText(
                                                    $this->user_result->getResult()->getUUID()
                                                )
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'Admin status'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['title' => $this->user_result->getResult()->getUUID()],
                                                $this->html_renderer->renderBooleanIcon($this->user_result->getResult()->getAdminStatus())
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'Skin icon'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['class' => 'skin-icon'],
                                                $this->html_renderer->renderTag(
                                                    'img',
                                                    [
                                                        'src' => 'data:image/png;base64,' . $this->skin_icon_base64,
                                                        'alt' => ''
                                                    ]
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            $this->html_renderer->renderTag(
                                'div',
                                ['class' => 'col-md box'],
                                $this->html_renderer->renderTag(
                                    'h1',
                                    [],
                                    $this->html_renderer->renderText(
                                        'Kill stats'
                                    )
                                ),
                                $this->html_renderer->renderTag(
                                    'table',
                                    [],
                                    $this->html_renderer->renderTag(
                                        'tbody',
                                        [],
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'Kills'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    (string)$this->user_result->getResult()->getKills()
                                                )
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'World Rank'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['title' => $this->user_result->getResult()->getUUID()],
                                                $this->html_renderer->renderText(
                                                    (string)$this->rank_handler->kills_rank . ' (' . round($this->rank_handler->kills_rank_percentage * 100, 3) . '%)'
                                                )
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'K/D ratio'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['title' => $this->user_result->getResult()->getUUID()],
                                                $this->html_renderer->renderBooleanIcon($this->user_result->getResult()->getAdminStatus())
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'Skin icon'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['class' => 'skin-icon'],
                                                $this->html_renderer->renderTag(
                                                    'img',
                                                    [
                                                        'src' => 'data:image/png;base64,' . $this->skin_icon_base64,
                                                        'alt' => ''
                                                    ]
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            $this->html_renderer->renderTag(
                                'div',
                                ['class' => 'col-md box'],
                                $this->html_renderer->renderTag(
                                    'h1',
                                    [],
                                    $this->html_renderer->renderText(
                                        'Profile Info'
                                    )
                                ),
                                $this->html_renderer->renderTag(
                                    'table',
                                    [],
                                    $this->html_renderer->renderTag(
                                        'tbody',
                                        [],
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'Username'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    $this->user_result->getResult()->getUsername()
                                                )
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'UUID'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['title' => $this->user_result->getResult()->getUUID()],
                                                $this->html_renderer->renderText(
                                                    $this->user_result->getResult()->getUUID()
                                                )
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'Admin status'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['title' => $this->user_result->getResult()->getUUID()],
                                                $this->html_renderer->renderBooleanIcon($this->user_result->getResult()->getAdminStatus())
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'Skin icon'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['class' => 'skin-icon'],
                                                $this->html_renderer->renderTag(
                                                    'img',
                                                    [
                                                        'src' => 'data:image/png;base64,' . $this->skin_icon_base64,
                                                        'alt' => ''
                                                    ]
                                                )
                                            )
                                        )
                                    )
                                )
                            ),
                            $this->html_renderer->renderTag(
                                'div',
                                ['class' => 'col-md box'],
                                $this->html_renderer->renderTag(
                                    'h1',
                                    [],
                                    $this->html_renderer->renderText(
                                        'Profile Info'
                                    )
                                ),
                                $this->html_renderer->renderTag(
                                    'table',
                                    [],
                                    $this->html_renderer->renderTag(
                                        'tbody',
                                        [],
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'Username'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    $this->user_result->getResult()->getUsername()
                                                )
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'UUID'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['title' => $this->user_result->getResult()->getUUID()],
                                                $this->html_renderer->renderText(
                                                    $this->user_result->getResult()->getUUID()
                                                )
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'Admin status'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['title' => $this->user_result->getResult()->getUUID()],
                                                $this->html_renderer->renderBooleanIcon($this->user_result->getResult()->getAdminStatus())
                                            )
                                        ),
                                        $this->html_renderer->renderTag(
                                            'tr',
                                            [],
                                            $this->html_renderer->renderTag(
                                                'td',
                                                [],
                                                $this->html_renderer->renderText(
                                                    'Skin icon'
                                                )
                                            ),
                                            $this->html_renderer->renderTag(
                                                'td',
                                                ['class' => 'skin-icon'],
                                                $this->html_renderer->renderTag(
                                                    'img',
                                                    [
                                                        'src' => 'data:image/png;base64,' . $this->skin_icon_base64,
                                                        'alt' => ''
                                                    ]
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * @param $username
     * @return bool
     * @throws Exception
     */
    private function loadExternalData($username) {
        if(strpos($username, '_') !== false) {
            $this->legacy_username = true;
            return false;
        }

        $user_query = new ApiQuery('https://api.2b2t.dev/', 'stats', ['username' => $username], 'username');
        $lastkill_query = new ApiQuery('https://api.2b2t.dev/', 'stats', ['lastkill' => $username], 'lastkill');
        $lastdeath_query = new ApiQuery('https://api.2b2t.dev/', 'stats', ['lastdeath' => $username], 'lastdeath');

        if(!$this->user_result = $this->cache_handler->doQuery($this->io_handler, $user_query)) {
            return false;
        }

        if(!$this->last_kill_result = $this->cache_handler->doQuery($this->io_handler, $lastkill_query)) {
            return false;
        }

        if(!$this->last_death_result = $this->cache_handler->doQuery($this->io_handler, $lastdeath_query)) {
            return false;
        }

        return true;
    }
}