<?php
/**
 * Phanbook : Delightfully simple forum software
 *
 * Licensed under The GNU License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @link    http://phanbook.com Phanbook Project
 * @since   1.0.0
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */
namespace Phanbook\Controllers;

use Phanbook\Search\Indexer;
use Phanbook\Models\Posts;
use Phalcon\Mvc\View;

/**
 * Class SearchController
 */
class SearchController extends ControllerBase
{
    public function indexAction()
    {
        $this->tag->setTitle('Search Results');
        $q = $this->request->getQuery('q', 'string');
        $indexer = new Indexer();
        $posts = $indexer->search(['title' => $q, 'content' => $q], 50, true);

        if (!count($posts)) {
            $posts = $indexer->search(['title' => $q], 50, true);

            if (!count($posts)) {
                $this->flashSession->notice('There are no search results');
                return $this->response->redirect();
            }
        }

        $this->view->setVars(
            [
            'currentOrder'  => null,
            'object'        => $posts,
            'canonical'     => '',
            'totalPages'    => 1,
            'currentPage'   => null
            ]
        );
        //Pick overwirite view to render.
        return $this->view->pickCustom('search/index');
    }
    /**
     * Finds related posts
     */
    public function showRelatedAction()
    {
        if ($this->request->isAjax()) {
            $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
            $post = Posts::findFirstById($this->request->getPost('id'));
            if ($post) {
                $indexer = new Indexer();
                $posts = $indexer->search(
                    [
                        'title'    => $post->getTitle(),
                        'content'  => $post->getContent()
                    ],
                    5,
                    true
                );
                if (count($posts) == 0) {
                    $posts = $indexer->search(
                        [
                            'title'    => $post->title
                        ],
                        5,
                        true
                    );
                }
                $this->view->object = $posts;
            } else {
                $this->view->object = array();
            }
            return 1;
        }
    }
}
