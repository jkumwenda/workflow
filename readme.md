# RPLUS2



## Installing

### 1. Docker environment
Recommend to use the docker environment for developing.

```
$ docker-compose up -d
```



### 2. Config files
```
$ copy .env.example .env
```
Changes
- DB settings (see docker-compose.yml, or you can create the original DB in your local environment)
- If you don't want to use LDAP login, change `USE_LDAP_LOGIN` to false.
- If you want to change sysadmin password, change `SYSADMIN_PASSWORD` to anything you want.
- OLD DB settings (current RPLUS using one) are not required.



### 3. Web Server Settings
(*) Execute these commands in the `rplus-php-apache` container if you use the docker environment.

```
$ docker-compose exec rplus-php-apache bash
```

#### 3-1. Composer
```
$ composer update
$ composer dump-autoload -o
```

#### 3-2. Application key generation
```
$ php artisan key:generate
```

#### 3-3. npm

```
$ npm install
$ npm run dev
```
(*) `npm run dev` makes app.js / app.css files in the 'public' folder. <br>
They are integrated all js/css files in npm_modules directory.<br>
For more details, see `resources/js/app.js` and `resources/sass/app.scss`<br>
And also check `webpack.mix.js`, and https://laravel.com/docs/5.8/mix<br/>


#### 3-4. Create symbolic link for storage
```
$ php artisan storage:link
```


### 4. DB Server settings
(*) Not necessary to create DB if you use the docker environment.

#### create DB
``` sql
CREATE SCHEMA `rplus2` DEFAULT CHARACTER SET utf8mb4 ;
```

#### Import DB data
Get newest exported DB data from your colleagues.


### Access your local rplus web site

If you use docker environment, access to `http://127.0.0.1:8080/`

### Options
#### Debugger tool (option)
```
$ composer require barryvdh/laravel-debugbar --dev
$ php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"
```

add config on .env
```
.env

DEBUGBAR_ENABLED=true
```


## For Developing (editing)

### 1. When add npm modules
```
$ npm install ~ --save
```
You need some actions to copy js/css(sass) files to under public directory

#### 1-1. Add import
`resources/js/app.js` and `resources/sass/app.scss`
``` js
// resources/js/app.js

// Select2
require('select2/dist/js/select2');
```

``` scss
// resources/sass/app.scss

// Select2
@import "select2/src/scss/core";
```

#### 1-2. Execute npm run develop command
```
$ npm run dev
```

#### 1-3. Check `public/js/app.js` or `public/css/app.css`
Will be added js/css codes.
Check the target codes are existed.

## For Migration (editing)
1.
`<?php echo site_url('notifications'); ?>`
` {{route('notifications') }}`

-> web.php 確認

2. permission 確認

3. その他
`<?php>`

4. glyphicon
-> fa

5. bootstrap 3 -> 4
panel -> card

## Note (editing)
リメイクにあたり
my requisitions, briefcase, track requisition -> requisitions に統合

リメイクに当たり削ぎ落とした機能
- briefcase
- preview_requisition
- order
- report
- voucher
