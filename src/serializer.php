<?php
namespace wechat {
  final class serializer {

    /** 此类型不应该可以实例化... */
    protected function __construct(){}

    // build xml by DOMDocument
    protected static function build(\DOMDocument $document, \DOMNode $node, $data) {
      foreach ($data as $name => $value) {
        $iteratable = is_array($value) || is_object($value);
        if ($iteratable || strlen($value) > 0) {
          $newly = $document->createElement(is_int($name) ? 'item' : $name);
          if ($iteratable) self::build($document, $newly, $value); else $newly->appendChild($document->createTextNode($value));
          $node->appendChild($newly);
        }
      }
    }

    // convert DOMNode to stdClass...
    protected static function convert(\DOMNode $node) {

      $value = null;
      foreach ($node->childNodes as $item) {
        if (XML_ELEMENT_NODE === $item->nodeType && $item->hasChildNodes()) {
          $name = $item->nodeName;
          if ($value === null) $value = new \stdClass();
          $value->$name = self::convert($item);
        }
      }

      // return textContent if not contains any element...
      return isset($value) ?
        $value : trim($node->textContent);
    }

    /**
     * 将 XML 解析成 stdClass 实例, 但以下数据会被解析为 null:
     * <xml></xml>
     * <xml>\r\n</xml>
     * <xml><!-- comment --></xml>
     * <xml>\r\n<!-- comment --></xml>
     * <xml><property/></xml>
     *
     * @param string $data XML
     * @throws \InvalidArgumentException 给定的 XML 无效
     * @return \stdClass|null 结果集
     */
    public static function parse($data) {
      $dom = new \DOMDocument();
      if (!$dom->loadXML($data, LIBXML_COMPACT)) throw new \InvalidArgumentException(error_get_last()['message']);
      $data = self::convert($dom->documentElement);
      return $data ? $data : null;
    }

    /**
     * 将对象/数组序列化为 XML
     * @param object|array $data 对象/数组
     * @param string $name 根元素名称
     * @throws \InvalidArgumentException 给定的数据不是对象或数组, 无法序列化
     * @return string XML
     */
    public static function stringify($data, $name = 'xml') {

      // validate type...
      if (!is_array($data) && !is_object($data))
        throw new \InvalidArgumentException('the entity must be an array/object, please check');

      $document = new \DOMDocument('1.0', 'UTF-8');
      $document->appendChild(new \DOMElement( $name ));
      self::build($document, $document->documentElement, $data);
      return $document->saveXML();
    }

    /**
     * 创建 text 消息
     * @param string $from 发送者ID
     * @param string $to 接收者ID
     * @param string $content 具体内容
     * @return string XML
     */
    public static function text($from, $to, $content) {
      return self::stringify(array(
        'ToUserName' => $to,
        'FromUserName' => $from,
        'MsgType' => 'text',
        'CreateTime' => time(),
        'Content' => $content
      ));
    }

    /**
     * 创建 image 消息
     * @param string $from 发送者ID
     * @param string $to 接收者ID
     * @param string $media 图片上传到微信后得到的 MediaId
     * @return string XML
     */
    public static function picture($from, $to, $media) {
      return self::stringify(array(
        'ToUserName' => $to,
        'FromUserName' => $from,
        'MsgType' => 'image',
        'CreateTime' => time(),
        'Image' => array('MediaId' => $media)
      ));
    }

    /**
     * 创建 video 消息
     * @param string $from 发送者ID
     * @param string $to 接收者ID
     * @param string $media 视频上传到微信后得到的 MediaId
     * @param string $title 标题
     * @param string $description 描述
     * @return string XML
     */
    public static function video($from, $to, $media, $title, $description) {
      return self::stringify(array(
        'ToUserName' => $to,
        'FromUserName' => $from,
        'MsgType' => 'video',
        'CreateTime' => time(),
        'Video' => array(
          'MediaId' => $media,
          'Title' => $title,
          'Description' => $description
        )
      ));
    }

    /**
     * 创建 voice 消息
     * @param string $from 发送者ID
     * @param string $to 接收者ID
     * @param string $media 语音上传到微信后得到的 MediaId
     * @return string XML
     */
    public static function recording($from, $to, $media) {
      return self::stringify(array(
        'ToUserName' => $to,
        'FromUserName' => $from,
        'MsgType' => 'voice',
        'CreateTime' => time(),
        'Voice' => array('MediaId' => $media)
      ));
    }

    /**
     * 创建 news 消息
     * @param string $from 发送者ID
     * @param string $to 接收者ID
     * @param array $articles 零或多个新闻对象的数组
     * @throws \InvalidArgumentException 新闻对象无效, 必须包含如下属性: Title,Description,PicUrl,Url
     * @return string XML
     */
    public static function news($from, $to, array $articles) {

      // validate article...
      foreach ($articles as $article) {
        $value = (array)$article;
        if (!isset($value['Title'], $value['Description'], $value['PicUrl'], $value['Url'])) {
          throw new \InvalidArgumentException('invalid article found');
        }
      }

      return self::stringify(array(
        'ToUserName' => $to,
        'FromUserName' => $from,
        'MsgType' => 'news',
        'CreateTime' => time(),
        'ArticleCount' => count($articles),
        'Articles' => $articles
      ));
    }
  }
}