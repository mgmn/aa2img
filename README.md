# aa2img

入力されたテキストを任意のフォントで png 画像に変換するPHPスクリプトです。
MS P ゴシックを使用すると、某掲示板サイトで使われるようなアスキーアートがきれいに表示できます。

## requirements

* PHP 5.4.0 以上が動くサーバ環境
* PHP GD
* 描画に使用する TrueType font (.ttf) ファイル

## how to install

1. リポジトリをクローン
2. 描画に使用する TrueType font (.ttf) ファイルを `html/` に配置する
3. `<input name="font">` の `id` と `value` を .ttf ファイルのファイル名に合わせて変更する
4. `html/` を PHP の動作する web サーバに公開する
