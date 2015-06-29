<?php

namespace Enpowi\Files;

use RedBeanPHP\R;
use Enpowi\App;
use Enpowi\Files\File;

class Gallery
{
    public $name;
    public $description;
    public $userID;
    public $pictures = [];

    private $_bean = null;

    public function __construct($id = null, $bean = null)
    {
        if ($bean === null) {
            if ($id === null) {
                throw new \Exception('Need bean or id');
            }
            $bean = $this->_bean = R::findOne('gallery', ' id = :id ', ['id' => $id]);
        } else {
            $this->_bean = $bean;
        }
    }

    public function setName($value)
    {
        $this->bean()->name = $value;
        $this->name = $value;
        return $this;
    }

    public function setDescription($value)
    {
        $this->bean()->description = $value;
        $this->description = $value;
        return $this;
    }

    public function addPicture(File $value)
    {
        $this->bean()->ownFileList[] = $value->bean();
        $this->pictures[] = $value;
        return $this;
    }

    public function removePicture(File $value)
    {
        unset($this->bean()->ownFileList[$value->id]);
        return $this;
    }

    public function bean()
    {
        if ($this->_bean === null) {
            $bean = R::dispense('gallery');
            $bean->userID = App::user()->id();
            $this->_bean = $bean;
        }
        return $this->_bean;
    }

    public function save()
    {
        $bean = $this->bean();
        return R::store($bean);
    }

    public function id()
    {
        $this->bean()->id;
    }

    public static function galleries($userID, $pageNumber = 0)
    {
        $beans = R::findAll('gallery', ' user_id = :userID order by name limit :offset, :count', [
            'userID' => $userID,
            'offset' => $pageNumber * App::$pagingSize,
            'count' => App::$pagingSize
        ]);

        $galleries = [];

        foreach($beans as $bean) {
            $galleries[] = new Gallery($bean->id, $bean);
        }

        return $galleries;
    }

    public static function create($name, $description)
    {
        $bean = R::dispense('gallery');
        $gallery = new Gallery(null, $bean);
        return $gallery
            ->setName($name)
            ->setDescription($description)
            ->save();
    }

    public function delete()
    {
        $bean = $this->bean();
        R::trash($bean);
        return $this;
    }
}