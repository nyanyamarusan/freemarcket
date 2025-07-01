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
    ＊ .env のロケール設定は、以下のように変更してください。
     APP_LOCALE=ja
     APP_FAKER_LOCALE=ja_JP

    - APP_LOCALE=ja
    - APP_FAKER_LOCALE=ja_JP

    ＊ もし、変更後に設定が反映されていなかった場合、php artisan config:clear で、キャッシュクリアしてみてください。

4.  php artisan key:generate
5.  php artisan migrate
6.  php artisan db:seed

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

## Laravel Dusk について

- Dusk テスト実行時は .env.dusk.local にて APP_URL=http://host.docker.internal に設定してください。これは Docker コンテナからホストの Laravel サーバーへアクセスするための URL です。

## mailhog環境変数

- MAIL_MAILER=smtp
- MAIL_SCHEME=null
- MAIL_HOST=mailhog
- MAIL_PORT=1025
- MAIL_USERNAME=null
- MAIL_PASSWORD=null
- MAIL_FROM_ADDRESS="hello@example.com"
- MAIL_FROM_NAME="${APP_NAME}"
