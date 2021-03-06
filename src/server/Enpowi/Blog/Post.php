<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 4/22/15
 * Time: 10:14 PM
 */

namespace Enpowi\Blog;

use Aura\Session\Exception;
use Enpowi\App;
use RedBeanPHP\R;
use RedBeanPHP\RedException;
use WikiLingo\Parser;
use Enpowi\Users\User;
use Enpowi\Event;

class Post {
    public $id;
    public $name;
    public $content;
	public $cache;
	public $cacheShort;
    public $created;
	public $edited;
	/**
	 * @var \Enpowi\Users\User
	 */
    private $_user = null;
	public $contributorIds = [];
	public $publishedOn;

    private $_bean = null;

	public static $parser = null;
	public static $cacheShortLimit = 500;
	public static $cacheShortAllowedTagNames = [];

    public function __construct($name, $bean = null)
    {
        if ($bean === null) {
            $bean = $this->_bean = $bean = R::findOne('blog', ' name = ? ', [$name]);
        } else {
            $this->_bean = $bean;
        }

        if ($bean === null) {
            $this->name = $name;
        } else {
            $this->convertFromBean();
        }
    }

    public static function byId($id)
    {
        $bean = R::findOne('blog', ' id = ? ', [$id]);
        if ($bean !== null) {
            return new Post($bean->name, $bean);
        }

        return null;
    }

    private function convertFromBean()
    {
        $bean = $this->_bean;

        if (!$this->exists()) return;

        $this->id = $bean->getID();
        $this->name = $bean->name;
        $this->content = $bean->content;
		$this->cache = $bean->cache;
		$this->cacheShort = $bean->cacheShort;
	    $this->edited = strtotime($bean->edited);
        $this->created = strtotime($bean->created);
	    $this->contributorIds = explode(',', $bean->contributorIds);
        $this->_user = new User(null, $bean->user);
	    $publishedOn = $bean->publishedOn;
	    if ($publishedOn !== '') {
		    $this->publishedOn = strtotime($bean->publishedOn );
	    }
    }

    public function exists()
    {
        if ($this->_bean === null) {
            return false;
        } else {
            return true;
        }
    }

    public function replace($content = '', $updateCache = true)
    {
        if (empty($this->name)) throw new Exception('Blog post needs name before it can be saved');

	    $bean = $this->_bean;

	    if ($bean === null) {
		    $this->_bean = $bean = R::dispense('blog');
	    }

        $bean->name = $this->name;
        $this->content = $bean->content = $content;
	    $bean->edited = R::isoDateTime();
        $bean->created = $this->created ?: R::isoDateTime();

	    $otherUserBean = App::user()->bean();
        $bean->user = $this->_user !== null ? $this->_user->bean() : $otherUserBean;
	    $this->contributorIds[] = $otherUserBean->getID();
	    $this->contributorIds = array_unique( $this->contributorIds );
	    $bean->contributorIds = implode(',',$this->contributorIds);
	    if (!empty($this->publishedOn)) {
		    $bean->publishedOn = R::isoDateTime( $this->publishedOn );
	    } else {
		    $bean->publishedOn = null;
	    }

		if ($updateCache) {
			if (Event\Blog\Cache::length() > 0) {
				Event\Blog\Cache::pub($this);
			} else {
				$this->cache = $rendered = $this->render();

				$allowedTags = join('><', self::$cacheShortAllowedTagNames);
				if (strlen($allowedTags) > 0) {
					$allowedTags = '<' . $allowedTags . '>';
				} else {
					$allowedTags = null;
				}
				$noTagsDirty = strip_tags($rendered, $allowedTags);
				$noTags = preg_replace('!\s+!', ' ', $noTagsDirty);

				if (strlen($noTags) > self::$cacheShortLimit) {
					$noTags = substr($noTags, 0, self::$cacheShortLimit) . '...';
				}

				$this->cacheShort = $noTags;
			}

			$bean->cache = $this->cache;
			$bean->cacheShort = $this->cacheShort;
		}

	    $id = R::store( $bean );

		Event\Blog\Replace::pub($this);

		return $id;
    }

    public function render()
    {
		if (self::$parser === null) {
			self::$parser = new Parser;
		}
		$parser = self::$parser;
        return $parser->parse($this->content);
    }

	/**
	 * @param int $pageNumber
	 * @param bool|false $showAll
	 * @return Post[]
	 */
    public static function posts($pageNumber = 1, $showAll = false)
    {
        $beans = R::findAll('blog', '
            where
                true = :show_all
                or date(published_on) >= now()
	        order by created
	        limit :offset, :count',[
		        'offset' => App::pageOffset($pageNumber),
		        'count' => App::$pagingSize,
	            'show_all' => $showAll
        ]);

        $posts = [];

        foreach($beans as $bean) {
            $posts[] = new Post($bean->name, $bean);
        }

        return $posts;
    }

	/**
	 * @param bool|false $showAll
	 * @return float
	 */
	public static function pages($showAll = false)
	{
		return R::count('blog', '
			where
				true = :show_all
				or date(published_on) >= now()
			order by created', [
				'show_all' => $showAll
		]) / App::$pagingSize;
	}

	/**
	 * @param bool|false $showAll
	 * @return Post|null
	 */
	public static function mostRecentPost($showAll = false)
	{
		$bean = R::findOne('blog', '
			where
				true = :show_all
				or date(published_on) >= now()
			order by created limit 0, 1', [
				'show_all' => $showAll
		]);

		if ($bean !== null) {
			return new Post($bean->name, $bean);
		}

		return null;
	}

	/**
	 * @param User $user
	 * @param $name
	 * @return Post|null
	 */
	public static function userPost(User $user, $name) {
		$bean = R::findOne('blog', '
			user_id = :user_id
			and name = :name', [
			'user_id' => $user->bean()->getID(),
			'name' => $name
		]);

		if ($bean !== null) {
			return new Post($bean->name, $bean);
		}

		return null;
	}

	/**
	 * @param User $user
	 * @param int $pageNumber
	 * @param bool|false $showAll
	 * @return Post[]
	 */
	public static function userPosts(User $user, $pageNumber = 1, $showAll = false)
	{
		$beans = R::findAll('blog', '
			where
				user_id = :user_id
				and (
					true = :show_all
					or date(published_on) >= now()
				)
			order by created
			limit :offset, :count', [
                'user_id' => $user->bean()->getID(),
                'offset' => App::pageOffset($pageNumber),
                'count' => App::$pagingSize,
                'show_all' => $showAll
		]);


		$posts = [];

		foreach($beans as $bean) {
			$posts[] = new Post($bean->name, $bean);
		}

		return $posts;
	}

	/**
	 * @param User $user
	 * @param bool|false $showAll
	 * @return float
	 */
	public static function userPages(User $user, $showAll = false)
	{
		return R::count('blog', '
			where
				user_id = :user_id
				and (
					true = :show_all
					or date(published_on) >= now()
				)
			order by created', [
			'user_id' => $user->bean()->getID(),
			'show_all' => $showAll
		]) / App::$pagingSize;
	}

	/**
	 * @param User $user
	 * @param bool|false $showAll
	 * @return Post|null
	 */
	public static function userMostRecentPost(User $user, $showAll = false)
	{
		$bean = R::findOne('blog', '
			where
				user_id = :user_id
				and (
					true = :show_all
					or date(published_on) >= now()
				)
			order by created limit 0, 1', [
			'user_id' => $user->bean()->getID(),
			'show_all' => $showAll
		]);

		if ($bean !== null) {
			return new Post($bean->name, $bean);
		}

		return null;
	}

	/**
	 * @return \RedBeanPHP\OODBBean
	 */
	public function bean()
	{
		if ($this->_bean === null) {
			$bean = $this->_bean = R::findOne( 'blog', ' name = ? ', [ $this->name ] );
			if ($bean !== null) {
				$this->_user = new User( null, $bean->user );
				$this->created = strtotime($bean->created);
			}
		}
		return $this->_bean;
	}

	/**
	 * @return User
	 */
	public function user()
	{
		return $this->_user;
	}
}