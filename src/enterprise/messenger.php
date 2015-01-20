<?php
namespace wechat\enterprise {

  /** Messenger */
  class messenger extends client {

    /**
     * Shorthand method...
     * @param string $data Data
     * @throws exception Remote exception
     * @return void
     */
    protected function send($data) {
      $this->execute(self::SEND, '/message/send?access_token='.rawurlencode($this->access_token()), $data);
    }

    /** Recipients list separator */
    const SEPARATOR = '|';

    /**
     * Send text message
     * @param int $sender Agent ID
     * @param string[] $recipients Recipients
     * @param string $content Content
     * @param bool $security Send security message
     * @return void
     */
    public function send_text($sender, array $recipients, $content, $security = true) {
      $this->send(array(
        'msgtype' => 'text',
        'safe' => $security,
        'touser' => implode(self::SEPARATOR, $recipients),
        'agentid' => $sender,
        'text' => array('content' => $content)
      ));
    }

    /**
     * Send image message
     * @param int $sender Agent ID
     * @param string[] $recipients Recipients
     * @param string $media_id A MediaId from https://qyapi.weixin.qq.com/cgi-bin/media/upload
     * @param bool $security Send security message
     * @return void
     */
    public function send_picture($sender, array $recipients, $media_id, $security = true) {
      $this->send(array(
        'msgtype' => 'image',
        'safe' => $security,
        'touser' => implode(self::SEPARATOR, $recipients),
        'agentid' => $sender,
        'image' => array('media_id' => $media_id)
      ));
    }

    /**
     * Send video message
     * @param int $sender Agent ID
     * @param string[] $recipients Recipients
     * @param string $media_id A MediaId from https://qyapi.weixin.qq.com/cgi-bin/media/upload
     * @param string $title Title
     * @param string $description Description
     * @param bool $security Send security message
     * @return void
     */
    public function send_video($sender, array $recipients, $media_id, $title = null, $description = null, $security = true) {

      $video = array();
      $video['media_id'] = $media_id;
      if (isset($title)) $video['title'] = $title;
      if (isset($description)) $video['description'] = $description;

      $this->send(array(
        'msgtype' => 'video',
        'safe' => $security,
        'touser' => implode(self::SEPARATOR, $recipients),
        'agentid' => $sender,
        'video' => $video
      ));
    }

    /**
     * Send voice message
     * @param int $sender Agent ID
     * @param string[] $recipients Recipients
     * @param string $media_id A MediaId from https://qyapi.weixin.qq.com/cgi-bin/media/upload
     * @param bool $security Send security message
     * @return void
     */
    public function send_recording($sender, array $recipients, $media_id, $security = true) {
      $this->send(array(
        'msgtype' => 'voice',
        'safe' => $security,
        'touser' => implode(self::SEPARATOR, $recipients),
        'agentid' => $sender,
        'voice' => array('media_id' => $media_id)
      ));
    }

    /**
     * Send news message
     * @param int $sender Agent ID
     * @param string[] $recipients Recipients
     * @param array $articles Zero or more entity
     * @throws \InvalidArgumentException Invalid entity in array, entity must includes Title, Description, PicUrl, Url properties
     * @return void
     */
    public function send_news($sender, array $recipients, array $articles) {

      // validate article...
      foreach ($articles as $article) {
        $value = (array)$article;
        if (!isset($value['Title'], $value['Description'], $value['PicUrl'], $value['Url'])) {
          throw new \InvalidArgumentException('invalid article found');
        }
      }

      $this->send(array(
        'msgtype' => 'mpnews',
        'touser' => implode(self::SEPARATOR, $recipients),
        'agentid' => $sender,
        'news' => $articles
      ));
    }

    /**
     * Send mpnews message
     * @param int $sender Agent ID
     * @param string[] $recipients Recipients
     * @param array $articles Zero or more entity
     * @param bool $security Send security message
     * @throws \InvalidArgumentException Invalid entity in array, entity must includes Title, Description, PicUrl, Url properties
     * @return void
     */
    public function send_newsplus($sender, array $recipients, array $articles, $security = true) {

      // validate article...
      foreach ($articles as $article) {
        $value = (array)$article;
        if (!isset($value['Title'], $value['Description'], $value['PicUrl'], $value['Url'])) {
          throw new \InvalidArgumentException('invalid article found');
        }
      }

      $this->send(array(
        'msgtype' => 'mpnews',
        'safe' => $security,
        'touser' => implode(self::SEPARATOR, $recipients),
        'agentid' => $sender,
        'news' => $articles
      ));
    }

    /**
     * Send file message
     * @param int $sender Agent ID
     * @param string[] $recipients Recipients
     * @param string $media_id A MediaId from https://qyapi.weixin.qq.com/cgi-bin/media/upload
     * @return void
     */
    public function send_file($sender, array $recipients, $media_id) {
      $this->send(array(
        'msgtype' => 'file',
        'safe' => $security,
        'touser' => implode(self::SEPARATOR, $recipients),
        'agentid' => $sender,
        'file' => array('media_id' => $media_id)
      ));
    }
  }
}