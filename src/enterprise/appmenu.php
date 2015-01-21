<?php
namespace wechat\enterprise {

  /** Application menu management client */
  class appmenu extends client {

    /**
     * Finds menu
     * @param int $id APPID
     * @return \stdClass
     */
    public function find($id) {
      return $this->execute(self::READ, '/menu/get?'.http_build_query(array(
        'access_token' => $this->access_token(),
        'agentid' => $id
      )));
    }

    /**
     * Creates or updates menu
     * @param int $id APPID
     * @param array[] $menus Menus
     * @return void
     */
    public function update($id, array $menus) {
      if (!empty($menus)) {
        $this->execute(self::SEND, '/menu/create?'.http_build_query(array(
          'access_token' => $this->access_token(),
          'agentid' => $id
        )), array(
          'button' => $menus
        ));
      }
    }

    /**
     * Clear all menu
     * @param int $id APPID
     * @return void
     */
    public function clear($id) {
      $this->execute(self::SEND, '/menu/delete?'.http_build_query(array(
        'access_token' => $this->access_token(),
        'agentid' => $id
      )));
    }

    /**
     * Creates a group contains one upto five button
     * @param string $name Name
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function group($name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->name = $name;
      $menu->sub_button = $childrens;
      return $menu;
    }

    /**
     * Creates a click button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function button($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'click';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates a view button
     * @param string $name Button text
     * @param string $url URL
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function link($name, $uri, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->type = 'view';
      $menu->sub_button = $childrens;
      $menu->url = $uri;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates a scancode_push button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function scanner($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'scancode_push';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates a scancode_waitmsg button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function waitable_scanner($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'scancode_waitmsg';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates a pic_sysphoto button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function camera($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'pic_sysphoto';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates a pic_photo_or_album button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function photo_selector($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'pic_photo_or_album';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates a pic_weixin button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function wechat_photo_selector($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'pic_weixin';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates a location_select button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function locator($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'location_select';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }
  }
}