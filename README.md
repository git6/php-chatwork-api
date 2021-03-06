# Chatwork API v1/v2 (APIトークン認証版) PHPラッパー
- - -
### v1の停止について
https://help.chatwork.com/hc/ja/articles/115000019401

### 向いている使い方
適当システムにずいっとincludeして即席で使いたい時とか
Wordpress等既存CMS、システムに持ち込んでその部分だけ独自でスクリプト書く時とか。

### 向いてない使い方
パッケージ管理とか入れてるキレイめなシステムや現場にはあまり向いてないです。


### 概要
よりシンプルに、このラッパー自体を変更して
   システムに合わせた処理で返せるようなつくり。


オブジェクトになっているが基本的に1エンドポイントにつき1メソッドで定義して
同じ対象をリストか単体取得の場合はまとめて、対象IDを渡した場合は単体を取得してくる。

### 命名規則
`methodEndpointName($parm)`

メソッドの後に変数(RoomIDとか)を抜いたエンドポイントで大体動く。
**パラメータもそのままだから**[ドキュメント](http://developer.chatwork.com/ja/ "ドキュメント")通りで動く感じ。
値が不正の場合、リクエストを送らないとか面倒な事はやってない
**全部レスポンスを貰って判断する**前提
バリデーションは値を渡す前に済ませておくか
レスポンスを見て判断すること。

### さくっと使いはじめる
```
require_once('chatwork-api_token_v2.php');
$chatwork = new chatwork();
$result = $chatwork->getMe();
```

[ChatWork API ドキュメント](http://developer.chatwork.com/ja/ "ChatWork API ドキュメント")