# CholoaniNET

CholoaniNET es un sistema para que empresas particulares así como particulares pueden hacer ofertas de trabajo a usuarios que se registren en ella. 

*Dependencias*
* PHP versión7
* R versión 3.4.4
* LSAfun
* RMySQL 
* MySql version 10.1.26
* Bootstrap 4
* Material.io icons
* GoogleMaps API


Instrucciones de instalación

Para los desesperados:
1. `$ git clone ssh://migrantech@lab.achichincle.net:2242/hikuri/migrantech/`
2. `$ nano migrantech/motores/interno/defs.php`. <br/>
	Modifcar la constante `DOMINIO` al dominio desde el cual se ejecutará la aplicación.
3. `$ mysql -u root -p -e "create database migrantech;"`
4. `$ mysql -u root -p -e "\. migrantech/modelodb.sql;" migrantech`
5. `$ mysql -u root -p -e "grant insert, select, update, execute on migrantech.* to usrcholoani@'localhost' identified by 'ch0l04n1N3T2018';"` 
6. `$ mysql -u root -p -e "call actualizaAccesos(1);" migrantech`
7. disfrutar... :)

Pasos detallados:
1. Clonar este proyecto en un directorio accesible para `www-data` o el manejador de Http que se esté usando, después en el directorio que se haya clonado el proyecto ejecutar.
2. En el archivo `defs.php` se establecen algunas constantes importantes para la ejecución de la aplicación, se pueden modificar según sea necesario. Es indispensable modificar la constante DOMINIO al dominio que se usará, ya que el sistema de protección de DDoS lo requiere para bloquear peticiones sospechosas.
3. Después procedemos a crear la base de datos que se va a usar. En el ejemplo se utiliza `'migrantech'` como nombre de base de datos, sin embargo, puede ser cualquiera.
4. Creamos una base de datos para usar.
5. Se ejecuta el script de creación de la base de datos. Es importante que se haga con el usuario root ya que el script incluye la creación de vistas y procedimientos almacenados.
6. El procedimiento almacenado `'actualizaAccesos()'` se encarga de proporcionar derechos de acceso a un usuario. Recibe como parámetro el ID de usuario y los usuarios se crean directamente en la tabla 'usuario'. Cabe señalar que el primer usuario que se use deberá tener un perfil `'3'`, y la seguridad final no será activada hasta después de terminar la implementación.
7. Una vez terminado, debemos de poder acceder a la instalación nueva de la aplicación. A partir de este momento, en el punto donde cambiemos la constante de producción a `"TRUE"` activará las funciones de seguridad. Por lo que es necesario que se haga después de establecidos los roles.

Se recommienda que se instalen manualmente las librerías de bootstrap y otras librerías JS adicionales



