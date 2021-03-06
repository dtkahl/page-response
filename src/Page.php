<?php namespace Dtkahl\Page;

use Dtkahl\ArrayTools\Collection;
use Dtkahl\ArrayTools\Map;
use Dtkahl\HtmlTagBuilder\HtmlTagBuilder;


/**
 * Class PageResponse
 * @package Dtkahl\PageResponse
 * @property Map $meta;
 * @property Map $options;
 * @property Map $sections;
 */
class Page
{

    private $_meta;
    private $_options;
    private $_scripts;
    private $_styles;
    private $_sections;
    private $_areas;

    /**
     * Page constructor.
     */
    public function __construct()
    {
        $this->_meta = new Map();
        $this->_options = new Map();
        $this->_sections = new Map();
        $this->_areas = new Map();
        $this->_scripts = new Collection();
        $this->_styles = new Collection();
    }

    /**
     * @param string $name
     * @return null
     */
    public function __get($name)
    {
        if (in_array($name, ['meta', 'options', 'sections'])) {
            return $this->{'_' . $name};
        }
        return null;
    }

    /**
     * @param $key
     * @param mixed|null $value
     * @return Map|mixed|null
     */
    public function section($key, $value = null)
    {
        if ($value == null) {
            return $this->_sections->get($key);
        }
        return $this->_sections->set($key, $value);
    }

    /**
     * @param $key
     * @return Collection
     */
    public function area($key)
    {
        if (!$this->_areas->has($key)) {
            $this->_areas->set($key, new Collection);
        }
        return $this->_areas->get($key);
    }

    /**
     * @param $key
     * @param mixed|null $value
     * @return Map|mixed|null
     */
    public function option($key, $value = null)
    {
        if ($value == null) {
            return $this->_options->get($key);
        }
        return $this->_options->set($key, $value);
    }

    /**
     * @param $key
     * @param mixed|null $value
     * @return Map|mixed|null
     */
    public function meta($key, $value = null)
    {
        if ($value == null) {
            return $this->_meta->get($key);
        }
        return $this->_meta->set($key, $value);
    }

    /**
     * @return string
     */
    public function renderMeta()
    {
        $html = [];
        foreach ($this->_meta->toArray() as $type => $value) {
            switch ($type) {

                case 'title':
                    $title = sprintf($this->options->get('title_pattern', '%s'), $value);
                    $html[] = (new HtmlTagBuilder('title', [], $title))->render();
                    $html[] = (new HtmlTagBuilder('meta', ['name' => 'twitter:title', 'content' => htmlentities($value)]))->render();
                    $html[] = (new HtmlTagBuilder('meta', ['property' => 'og:title', 'content' => htmlentities($value)]))->render();
                    break;

                case 'charset':
                    $html[] = (new HtmlTagBuilder('meta', ['charset' => $value]))->render();
                    break;

                case 'date':
                case 'copyright':
                case 'keywords':
                case 'viewport':
                case 'robots':
                case 'page-topic':
                case 'page-type':
                case 'og:type':
                case 'audience':
                case 'google-site-verification':
                case 'csrf-token':
                case 'twitter:site':
                    $html[] = (new HtmlTagBuilder('meta', ['name' => $type, 'content' => $value]))->render();
                    break;

                case 'twitter:card':
                case 'local':
                case 'og:site_name':
                    $html[] = (new HtmlTagBuilder('meta', ['property' => $type, 'content' => $value]))->render();
                    break;

                case 'description':
                    $html[] = (new HtmlTagBuilder('meta', ['name' => 'description', 'content' => htmlentities($value)]))->render();
                    $html[] = (new HtmlTagBuilder('meta', ['name' => 'twitter:description', 'content' => htmlentities($value)]))->render();
                    $html[] = (new HtmlTagBuilder('meta', ['property' => 'og:description', 'content' => htmlentities($value)]))->render();
                    break;

                case 'image':
                    $html[] = (new HtmlTagBuilder('meta', ['name' => 'twitter:image', 'content' => $value]))->render();
                    $html[] = (new HtmlTagBuilder('meta', ['property' => 'og:image', 'content' => $value]))->render();
                    break;

                case 'url':
                    $html[] = (new HtmlTagBuilder('meta', ['name' => 'twitter:url', 'content' => $value]))->render();
                    $html[] = (new HtmlTagBuilder('meta', ['property' => 'og:url', 'content' => $value]))->render();
                    break;

                case 'author':
                    $html[] = (new HtmlTagBuilder('meta', ['name' => 'author', 'content' => $value]))->render();
                    $html[] = (new HtmlTagBuilder('meta', ['property' => 'og:author', 'content' => $value]))->render();
                    break;

                case 'publisher':
                    $html[] = (new HtmlTagBuilder('meta', ['name' => 'publisher', 'content' => $value]))->render();
                    $html[] = (new HtmlTagBuilder('meta', ['property' => 'og:publisher', 'content' => $value]))->render();
                    break;

                case 'language':
                    $html[] = (new HtmlTagBuilder('meta', ['http-equiv' => 'content-language', 'content' => $value]))->render();
                    break;

                case 'raw':
                    $html[] = $value;
                    break;
            }
        }
        return implode("\n", $html);
    }

    /**
     * @param string|string[] $js
     * @return $this
     */
    public function addJavascript($js)
    {
        $this->_scripts->merge((array)$js);
        return $this;
    }

    /**
     * @param string|string[] $css
     * @return $this
     */
    public function addStylesheet($css)
    {
        $this->_styles->merge((array)$css);
        return $this;
    }

    /**
     * @return string
     */
    public function renderJavascripts()
    {
        return $this->_scripts->copy()->map(function ($js) {
            $attributes = [
                'type' => "text/javascript",
                'src' => $this->options->get('js_path', '/js') . "/$js.js",
            ];
            if ($this->options->get('js_async', false)) {
                $attributes[] = 'async';
            }
            return (new HtmlTagBuilder('script', $attributes))->render();
        })->join("\n");
    }

    /**
     * @return string
     */
    public function renderStylesheets()
    {
        return $this->_styles->copy()->map(function ($css) {
            $attributes = [
                'type' => "text/css",
                'rel' => "stylesheet",
                'href' => $this->options->get('css_path', '/css') . "/$css.css",
            ];
            return (new HtmlTagBuilder('link', $attributes))->render();
        })->join("\n");
    }

}
