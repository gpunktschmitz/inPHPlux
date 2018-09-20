# inPHPlux

This project was created to ease the input using my mobile phone into my very own, very private, not secured influx database at home. It is known not to be secured or failure-proof. It could however be secured by creating a `.htaccess` file and request a user and password.

## structure

The file `index.php` iterates through all `.ini` files in the folder `forms` and lists them as links:

<img src="https://github.com/gpunktschmitz/inPHPlux/raw/master/README.images/forms-list.png">

## ini-file example

```ini
database=statistics

[tagsets]
tankstelle=agip
strasse=

[valuesets]
euro=
liter=
preisproliter=
```

### database

This value must be set to an existing database.

### [tagsets] (optional)

For each tag create a line. A default value (which can be edited/overwritten in the form) can be set optionally.

### [valuesets]

At least one valueset must be specified. A default value can be set optionally

## the form

<img src="https://github.com/gpunktschmitz/inPHPlux/raw/master/README.images/example-form.png">

### measurement

The measurement name is pre-set to the ini-filename (`auto_tanken.ini` results in `auto_tanken`) and can be edited.

### datetime

The default value is the current timestamp but can be changed either by editing the input string or using the datetime picker.

<img src="https://github.com/gpunktschmitz/inPHPlux/raw/master/README.images/example-form.png">

