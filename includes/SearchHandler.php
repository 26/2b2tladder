<?php


class SearchHandler
{
    const SEARCH_LIMIT = 32;

    /**
     * @var bool
     */
    public $is_loaded = false;

    /**
     * @var DatabaseHandler
     */
    private $database;

    /**
     * @var array
     */
    private $results;

    /**
     * @var HtmlRenderer
     */
    private $html_renderer;

    /**
     * @var CacheHandler
     */
    private $cache_handler;

    /**
     * SearchHandler constructor.
     */
    public function __construct()
    {
        $this->database = DatabaseHandler::newFromConfig();
        $this->html_renderer = new HtmlRenderer();
    }

    /**
     * @param $search_term
     * @return SearchHandler
     */
    public function doSearch($search_term) {
        $this->is_loaded = true; // TODO: Error handling

        if(!$search_term) {
            $this->results = [];

            return $this;
        }

        if(!is_string($search_term)) {
            throw new InvalidArgumentException("Search term must be of type string.");
        }

        try {
            $results = $this->performSearch($search_term);
        } catch(Exception $e) {
            // TODO: Error message.

            $this->results = [];

            return $this;
        }

        $this->is_loaded = true;
        $this->results = $results;

        return $this;
    }

    /**
     * Renders the results and returns them.
     *
     * @return Tag
     * @throws Exception
     */
    public function renderSearch() {
        if(!$this->is_loaded) {
            throw new LogicException("Tried to render results without performing search.");
        }

        if(!$this->results) {
            return $this->html_renderer->renderText("No results found.");
        }

        return $this->renderSearchTable(
            $this->renderSearchResults()
        );
    }

    /**
     * @param array $content
     * @return Tag
     * @throws Exception
     */
    public function renderSearchTable($content) {
        return $this->html_renderer->renderTag(
            'div',
            ['class' => 'search-results'],
            $this->html_renderer->renderTag(
                'table',
                ['class' => 'table'],
                $this->html_renderer->renderTag(
                    'thead',
                    ['class' => 'blue'],
                    $this->html_renderer->renderTag(
                        'tr',
                        [],
                        $this->html_renderer->renderTag(
                            'th',
                            [],
                            $this->html_renderer->renderText(
                                ''
                            )
                        ),
                        $this->html_renderer->renderTag(
                            'th',
                            [],
                            $this->html_renderer->renderText(
                                'Username'
                            )
                        )
                    )
                ),
                $this->html_renderer->renderTag(
                    'tbody',
                    [],
                    ...$content
                )
            )
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    public function renderSearchResults() {
        if(!$this->results || !$this->is_loaded) {
            throw new LogicException("Tried to render search before it is loaded.");
        }

        if(!$this->html_renderer) {
            $this->html_renderer = new HtmlRenderer();
        }

        if(!$this->cache_handler) {
            $this->cache_handler = new CacheHandler();
        }

        $rows = [];

        foreach($this->results as $row) {
            $rows[] = $this->html_renderer->renderTag(
                'tr',
                [
                    'class' => 'userpage search-item',
                    'data-username' => $row['username']
                ],
                $this->html_renderer->renderTag(
                    'td',
                    ['class' => 'skin-image'],
                    $this->html_renderer->renderSkin(
                        $this->cache_handler->loadSkin($row['uuid'])
                    )
                ),
                $this->html_renderer->renderTag(
                    'td',
                    [],
                    $this->html_renderer->renderText(
                        $row['username']
                    )
                )
            );
        }

        if(count($this->results) === self::SEARCH_LIMIT) {
            $rows[] = $this->html_renderer->renderTag(
                'tr',
                [
                    'class' => 'search-cond'
                ],
                $this->html_renderer->renderTag(
                    'td',
                    ['colspan' => '2',],
                    $this->html_renderer->renderText(
                        'There might be more results... Please try a narrower search term.'
                    )
                )
            );
        }

        return $rows;
    }

    /**
     * @param $search_term
     * @return array
     */
    private function performSearch($search_term) {
        // TODO: Improve search

        $statement = $this->database->getConnection()->prepare("SELECT uuid, username FROM " . DatabaseHandler::USER_CACHE_TABLE . " WHERE `username` LIKE ? OR `uuid` = ? OR `uuid` = ? ORDER BY `joins` DESC, `kills` DESC, `deaths` DESC, `leaves` DESC LIMIT " . self::SEARCH_LIMIT . "");
        $statement->execute([
            '%' . $search_term . '%',
            $search_term,
            $this->untrimUUID($search_term)
        ]);

        return $statement->fetchAll();
    }

    /**
     * Untrims a given UUID if it is valid.
     *
     * @param $search_term
     * @return mixed
     */
    private function untrimUUID($search_term) {
        if(strlen($search_term) !== 32) {
            return $search_term;
        }

        $uuid_parts[] = substr($search_term, 0, 8);
        $uuid_parts[] = substr($search_term, 8, 4);
        $uuid_parts[] = substr($search_term, 12, 4);
        $uuid_parts[] = substr($search_term, 16, 4);
        $uuid_parts[] = substr($search_term, 20, 12);

        return implode('-', $uuid_parts);
    }
}