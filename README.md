# フリマアプリ

## 環境構築

Docker ビルド

1.  git clone git@github.com:nyanyamarusan/freemarcket.git
2.  docker-compose up -d --build

＊ MySQL は、OS によって起動しない場合があるのでそれぞれの PC に合わせて docker-compose.yml ファイルを編集してください。

Laravel 環境構築

1.  docker-compose exec php bash
2.  composer install
3.  .env.example ファイルから.env を作成し、環境変数を変更
4.  php artisan key:generate
5.  php artisan migrate
6.  php artisan db:seed

### .envファイルの設定

- ロケール設定

    - APP_LOCALE=ja
    - APP_FAKER_LOCALE=ja_JP

- セッション、キャッシュ設定

    - SESSION_DRIVER=file
    - CACHE_STORE=file

- mailhog 環境変数

    - MAIL_MAILER=smtp
    - MAIL_SCHEME=null
    - MAIL_HOST=mailhog
    - MAIL_PORT=1025
    - MAIL_USERNAME=null
    - MAIL_PASSWORD=null
    - MAIL_FROM_ADDRESS="hello@example.com"
    - MAIL_FROM_NAME="${APP_NAME}"

- stripe 環境変数

    - STRIPE_KEY= あなたの公開可能キー
    - STRIPE_SECRET= あなたの秘密キー

＊ もし、変更後に設定が反映されていなかった場合、php artisan config:clear で、キャッシュクリアしてみてください。

## 使用技術

- PHP 8.3-fpm
- Laravel 12.14.1
- MySQL 8.0.41
- nginx 1.26.3
- Fortify
- mailhog
- Stripe
- Laravel Dusk（ブラウザテスト）

## ER 図

![ER図](/freemarcket.drawio.png)

## URL

- 開発環境：http://localhost/
- ユーザー登録：http://localhost/register
- phpMyAdmin：http://localhost:8080/

## ログインユーザー情報（ユーザー：計3人）

- メールアドレス：ファクトリにてユーザーを作成しているため、各ユーザーのメールアドレスはダミーデータ作成後、phpMyAdminでご確認ください。
- パスワード：password  
＊ パスワードは全ユーザー共通です。

## 機能要件「チャットを入力した状態で他の画面に遷移しても、入力情報を保持できる（本文のみ）」について

- 本要件は、「開発プロセス」内の「開発言語はCOACHTECHの教材内の言語を使用すること」の条件、つまり、JavaScript使用不可のため、フォーム送信前の入力情報の保持は実現困難と判断し、以下の場合に限定して入力情報を保持するように実装しています。  
  - フォーム送信後
  - バリデーションエラーにより、リクエストが差し戻された場合

## Laravel Dusk について

- Dusk テスト実行時は .env.dusk.local にて APP_URL=http://host.docker.internal に設定してください。これは Docker コンテナからホストの Laravel サーバーへアクセスするための URL です。
