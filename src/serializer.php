<?php
namespace wechat {
  /** Wechat xml serializer */
  final class serializer {

    /** This class should not instantiated */
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
     * Parse xml as \stdClass instance, besides following string:
     * <xml></xml>
     * <xml>\r\n</xml>
     * <xml><!-- comment --></xml>
     * <xml>\r\n<!-- comment --></xml>
     * <xml><property/></xml>
     *
     * @param string $data XML
     * @throws \InvalidArgumentException Malformed xml
     * @return \stdClass|null
     */
    public static function parse($data) {
      $dom = new \DOMDocument();
      if (!$dom->loadXML($data, LIBXML_COMPACT)) throw new \InvalidArgumentException(error_get_last()['message']);
      $data = self::convert($dom->documentElement);
      return $data ? $data : null;
    }

    /**
     * Serialize array/object as xml
     * @param object|array $data An array/object
     * @param string $name Root element name
     * @throws \InvalidArgumentException Data not an array/object
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
     * Creates text message
     * @param string $from Sender ID
     * @param string $to Recipient ID
     * @param string $content Content
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
     * Creates image message
     * @param string $from Sender ID
     * @param string $to Recipient ID
     * @param string $media A MediaId from https://qyapi.weixin.qq.com/cgi-bin/media/upload
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
     * Creates video message
     * @param string $from Sender ID
     * @param string $to Recipient ID
     * @param string $media A MediaId from https://qyapi.weixin.qq.com/cgi-bin/media/upload
     * @param string $title Title
     * @param string $description Description
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
     * Creates voice message
     * @param string $from Sender ID
     * @param string $to Recipient ID
     * @param string $media A MediaId from https://qyapi.weixin.qq.com/cgi-bin/media/upload
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
     * Creates news message
     * @param string $from Sender ID
     * @param string $to Recipient ID
     * @param array $articles Zero or more entity
     * @throws \InvalidArgumentException Invalid entity in array, entity must includes Title, Description, PicUrl, Url properties
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