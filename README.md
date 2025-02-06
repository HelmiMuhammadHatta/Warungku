## Cara Install
#### 1. Clone Project
```bash
git clone https://github.com/HelmiMuhammadHatta/Warungku
```

#### 2. Pindahkan file ke directory apache
* Windows : c:xampp/htdocs/
* Linux : /var/www/html/

#### 3. Edit conf/globalvar.php
edit sesuai dengan url index aplikasi.
```php
    $base_url = "http://localhost/simpel-kasir-master/" ;
```

#### 4. Set-up database
buat database baru dengan nama db_toko
```sql
    create database db_toko ;
```
export db_toko.sql ke database baru tersebut.

## Program Pendukung
- Bootstrap Studio
- Visual Studio Code
- xampp
