<?php
namespace wechat\enterprise {

  /** Application menu management client */
  class appmenu extends client {

    /**
     * Finds application's menu
     * @param int $id Application identifier
     * @return \stdClass
     */
    public function find($id) {
      return $this->execute(self::READ, '/menu/get?'.http_build_query(array(
        'access_token' => $this->access_token(),
        'agentid' => $id
      )));
    }

    /**
     * Creates or updates application's menu
     * @param int $id Application identifier
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
     * Clear application's menu
     * @param int $id Application identifier
     * @return void
     */
    public function clear($id) {
      $this->execute(self::SEND, '/menu/delete?'.http_build_query(array(
        'access_token' => $this->access_token(),
        'agentid' => $id
      )));
    }

    /**
     * Creates an object represents click-only button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function create_button($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'click';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates an object represents view button
     * @param string $name Button text
     * @param string $url URL
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function create_view($name, $uri, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->type = 'view';
      $menu->sub_button = $childrens;
      $menu->url = $uri;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates an object represents scancode_push button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function create_scanner($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'scancode_push';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates an object represents scancode_waitmsg button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function create_waitable_scanner($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'scancode_waitmsg';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates an object represents pic_sysphoto button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function create_camera($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'pic_sysphoto';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates an object represents pic_photo_or_album button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function create_photo_selector($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'pic_photo_or_album';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates an object represents pic_weixin button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function create_wechat_photo_selector($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'pic_weixin';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }

    /**
     * Creates an object represents location_select button
     * @param string $id Button key
     * @param string $name Button text
     * @param \stdClass[] $childrens Childrens
     * @return \stdClass
     */
    public static function create_locator($id, $name, array $childrens = array()) {
      $menu = new \stdClass();
      $menu->key = $id;
      $menu->type = 'location_select';
      $menu->sub_button = $childrens;
      $menu->name = $name;
      return $menu;
    }
  }
}