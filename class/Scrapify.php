<?php
namespace Scraper;

use Goutte\Client;

class Scrapify {

    /**
     * @var Client
     */
    private $goutteClient;

    /**
     * @var Object
     */
    private $scrapResult;

    public function __construct(private String $searchParam) {
        $this->goutteClient = new Client();
    }

    public function urlInjector($url) {
        return $this->goutteClient->request('GET', $url);
    }

    /**
     * @return Object
     * @throws Exception
     */
    public function formCrawler()  {
        try {
            return self::urlInjector('https://search.ipaustralia.gov.au/trademarks/search/advanced')
            ->filter('#basicSearchForm');
        } catch (\Throwable $ex) {
            throw new \Exception("Something went wrong while finding form", 1);
        }
    }

    /**
     * @return Object
     * @throws Exception
     */
    public function formSubmitter() {
        try {
            return  $this->goutteClient->submit($this->formCrawler()->form(), ['wv[0]' => $this->searchParam]);
        } catch (\Throwable $ex) {
            throw new \Exception("Error Processing Request");   
        }
    }

    /**
     * @return int
     */
    public function getPaginationCount() {
       $rawCount = $this->formSubmitter()->filter('.qa-count')->text();
       if ((int) $rawCount > 100) {
            return $totalPagination = ((int) $rawCount > 999 ?: str_replace(',', '', $rawCount)) / 100;
       }

       return true;
    }

    public function extract() {
        $paginationCount = $this->getPaginationCount();
        $result = $res= [];
        $count = 0;
        for($i=0; $i < $paginationCount; $i++) {        
            $result[] = $this->hardWorker($i);
        }

        foreach ($result as $sub) {
            foreach ($sub as $index => $val) {
                $res[$index][] = $val;
            }
        }
        
        return json_encode($res);
    }
    
    /**
     * @param String $idOrClass
     * @param int $pageNumber
     * @param boolean $isImage
     */
    private function hardWorker($pageNumber) {
        $collector = [
            'number' => [],
            'logo_url' => [],
            'name' => [],
            'classes' => [],
            'status1' => [],
            'status2' => [],
            'details_url_page' => []
        ];
        $counter = 1;
        $pageCriteria =  '&p=' . $pageNumber;
        if ($pageNumber == 0) {
            $pageCriteria = '';
        }
        $link = array_values((array)$this->formSubmitter())[0]. $pageCriteria;
        $url = $this->urlInjector($link);
        
        $url->filter('.trademark img')->each(function ($node) use (&$collector) {
                $collector['logo_url'][] = $node->attr('src');
        });
        
        $url->filter('.qa-tm-number')->each(function ($node) use (&$collector) {
            $collector['number'][] = $node->text();
        });

        $url->filter('.qa-tm-number')->each(function ($node) use (&$collector) {
            $collector['details_url_page'][] = $node->attr('href');
        });

        $url->filter('.result .classes')->each(function ($node) use (&$collector) {
            $collector['classes'][] = $node->text();
        });

        $url->filter('.status i span')->each(function ($node) use (&$collector) {
            $collector['status'][] = $node->attr('href');
        });

        $url->filter('.result .words')->each(function ($node) use (&$collector) {
            $collector['name'][] = $node->text();
        });

        return $collector;
    }
}