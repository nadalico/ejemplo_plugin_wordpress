<?php
/*
  Plugin Name: ExamplePlugin
  Plugin URI: http://actiocio.es
  Description: Ejemplo de plugin con clases
  Version: 1.0
  Author: Javier Nadal
  Author URI: http://web
 */

//creamos la clase con el nombre del archivo
class ExamplePlugin{
	//en el constructor es donde llamamos a las acciones que vayamos creando
	public function __construct() {
		add_action('admin_menu',array($this,"add_admin_menu"));
	 	add_action('admin_menu',array($this,"add_option_menu"));
	 	add_option( 'db_version', $db_version );

	 	global $wpdb;
	 	$this->wpdb = $wpdb;
	 	$this->tablename = $wpdb->prefix . 'ejemplo_plugin';
	 }

	 /*añadimos tabla en la base de datos*/
	public function plugin_example_install()
	{
		//global $wpdb;
	    global $db_version;
	    //$table_name = $wpdb->prefix . 'ejemplo_plugin';
	    $charset_collate = $this->wpdb->get_charset_collate();

	    $sql = "CREATE TABLE $this->tablename (
	                id INT NOT NULL AUTO_INCREMENT,
	                prueba varchar(40) NOT NULL,
	                PRIMARY KEY (id) )";

	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	    dbDelta( $sql );
	}

	public function tablename(){
		global $wpdb;
	 	$tablename = $wpdb->prefix . 'ejemplo_plugin';
	 	return $tablename;
	}

	//setup_admin_menu hace que en la zona de administración de wordpress se añada
	//otra opción en el menú de la izquierda, donde pone página, comentarios etc
	//eso es así porque utilizamos add_object_page llamando a la función callback admin_page
	//y decimos que se lleve a cabo al activar el plugin con activate_plugins
	public function add_admin_menu(){
		//menu
	  	add_menu_page('ExamplePlugin', 'ExamplePlugin', 'activate_plugins', 'ejemplo-plugin.php', array($this, 'admin_page'));
	  	//submenus
	  	add_submenu_page( 'ejemplo-plugin.php', 'Añadir Nuevo', 'Añadir Nuevo', 'manage_options', 'options_submenu1',  array($this, 'form_function'));
		add_submenu_page( 'ejemplo-plugin.php', 'Opciones', 'Opciones', 'manage_options', 'options_submenu2',  array($this, 'options_function'));
	}

	//el mensaje que saldrá en otra página al pulsar el botón del menú que hemos creado
	public function admin_page(){
		$exampleListTable = new Example_List_Table();
        $exampleListTable->prepare_items();
        ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <p>
                	<h2>Ejemplo Listado Prueba</h2>
                	<a href="admin.php?page=options_submenu1" class="page-title-action">Añadir Nuevo</a>
            	</p>
                <?php $exampleListTable->display(); ?>
            </div>
		<?php
	}

	/*funciones submenus*/
	public function form_function(){
		?>
	  	<h2>Ejemplo plugin</h2>
	  	<form name="plugin_example" action="" method="post">
		  	<table class='form-table'>
		  		<tbody>
		  			<tr class='user-email-wrap'>
		  				<th>
		  					<label for='prueba'>Prueba</label>
		  				</th>
		  				<td>
		  					<input type='text' class='regular-text ltr' id="prueba" name="prueba" placeholder='prueba' />
		  				</td>
		  			</tr>

		  		</tbody>
		  	</table>
		  	<input type='submit' class='button button-primary' id="submit" value="Añadir" />
	  	</form>
	  	<?php

	  	if ( $_SERVER['REQUEST_METHOD'] == 'POST'){
	  		echo "Guardado";
	  		global $wpdb;
	  		$tablename = $wpdb->prefix . 'ejemplo_plugin';
	  		$data = [
	  		    'prueba' => $_POST[ 'prueba'],
	  		];
	  		$wpdb->insert($tablename, $data);
	  	}
	}

	public function form_save_data(){

	}

	public function options_function(){
		?>
			<div>Opciones plugin ejemplo</div>
		<?php
	}

	//lo mismo que el anterior, pero en vez de usar add_object_page usamos add_options_page
	//el cuál en la pestaña ajustes crea una opción que se llama Poo_plugin llamando a la función
	//callback admin_menu
	public function add_option_menu(){
	  	add_options_page("ExamplePlugin", "ExamplePlugin", "read", __FILE__,array($this, 'admin_menu'));
	}

	public function admin_menu(){
		?>
	  	<div>UN PLUGIN EN WORDPRESS CON POO</div>
	  	<?php
	}
}



//creamos la instancia para poder utilizarlo
if(is_admin())
{
	$miplugin = new ExamplePlugin();
	register_activation_hook( __FILE__, array($miplugin,'plugin_example_install') );
}

//clase listado

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Example_List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = array();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->items = $data;
    }

     /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
        	'cb'    => '<input type="checkbox" />',
            'prueba'       => 'Prueba',
        );
        return $columns;
    }
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }
    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('title' => array('title', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
    	global $wpdb;
	 	$tablename = $wpdb->prefix . 'ejemplo_plugin';

       //$total_items = $wpdb->get_var("SELECT COUNT(id) FROM $tablename");
       //$items = $wpdb->get_var("SELECT * FROM $tablename");
        $data = $wpdb->get_results("SELECT * FROM $tablename",ARRAY_A);


        /*$data[] = array(
                    'id'          => 1,
                    'title'       => 'The Shawshank Redemption',
                    'description' => 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.',
                    'year'        => '1994',
                    'director'    => 'Frank Darabont',
                    'rating'      => '9.3'
                    );
        $data[] = array(
                    'id'          => 2,
                    'title'       => 'The Godfather',
                    'description' => 'The aging patriarch of an organized crime dynasty transfers control of his clandestine empire to his reluctant son.',
                    'year'        => '1972',
                    'director'    => 'Francis Ford Coppola',
                    'rating'      => '9.2'
                    );
        $data[] = array(
                    'id'          => 3,
                    'title'       => 'The Godfather: Part II',
                    'description' => 'The early life and career of Vito Corleone in 1920s New York is portrayed while his son, Michael, expands and tightens his grip on his crime syndicate stretching from Lake Tahoe, Nevada to pre-revolution 1958 Cuba.',
                    'year'        => '1974',
                    'director'    => 'Francis Ford Coppola',
                    'rating'      => '9.0'
                    );
        $data[] = array(
                    'id'          => 4,
                    'title'       => 'Pulp Fiction',
                    'description' => 'The lives of two mob hit men, a boxer, a gangster\'s wife, and a pair of diner bandits intertwine in four tales of violence and redemption.',
                    'year'        => '1994',
                    'director'    => 'Quentin Tarantino',
                    'rating'      => '9.0'
                    );
        $data[] = array(
                    'id'          => 5,
                    'title'       => 'The Good, the Bad and the Ugly',
                    'description' => 'A bounty hunting scam joins two men in an uneasy alliance against a third in a race to find a fortune in gold buried in a remote cemetery.',
                    'year'        => '1966',
                    'director'    => 'Sergio Leone',
                    'rating'      => '9.0'
                    );
        $data[] = array(
                    'id'          => 6,
                    'title'       => 'The Dark Knight',
                    'description' => 'When Batman, Gordon and Harvey Dent launch an assault on the mob, they let the clown out of the box, the Joker, bent on turning Gotham on itself and bringing any heroes down to his level.',
                    'year'        => '2008',
                    'director'    => 'Christopher Nolan',
                    'rating'      => '9.0'
                    );
        $data[] = array(
                    'id'          => 7,
                    'title'       => '12 Angry Men',
                    'description' => 'A dissenting juror in a murder trial slowly manages to convince the others that the case is not as obviously clear as it seemed in court.',
                    'year'        => '1957',
                    'director'    => 'Sidney Lumet',
                    'rating'      => '8.9'
                    );
        $data[] = array(
                    'id'          => 8,
                    'title'       => 'Schindler\'s List',
                    'description' => 'In Poland during World War II, Oskar Schindler gradually becomes concerned for his Jewish workforce after witnessing their persecution by the Nazis.',
                    'year'        => '1993',
                    'director'    => 'Steven Spielberg',
                    'rating'      => '8.9'
                    );
        $data[] = array(
                    'id'          => 9,
                    'title'       => 'The Lord of the Rings: The Return of the King',
                    'description' => 'Gandalf and Aragorn lead the World of Men against Sauron\'s army to draw his gaze from Frodo and Sam as they approach Mount Doom with the One Ring.',
                    'year'        => '2003',
                    'director'    => 'Peter Jackson',
                    'rating'      => '8.9'
                    );
        $data[] = array(
                    'id'          => 10,
                    'title'       => 'Fight Club',
                    'description' => 'An insomniac office worker looking for a way to change his life crosses paths with a devil-may-care soap maker and they form an underground fight club that evolves into something much, much more...',
                    'year'        => '1999',
                    'director'    => 'David Fincher',
                    'rating'      => '8.8'
                    );*/
        return $data;
    }
    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'prueba':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'prueba';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }
        $result = strcmp( $a[$orderby], $b[$orderby] );
        if($order === 'asc')
        {
            return $result;
        }
        return -$result;
    }

    /**
     * desplegable para acción en masa del encabezado
     */
     function get_bulk_actions()
     {
       	$actions = array(
         	'delete'    => 'Eliminar'
       	);
       	return $actions;
     }

      /**
     * checkbox, el campo name debe ser por el que vamos a realizar acciones en masa
     */

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />', $item['id']
        );
    }

     /**
     * Procesa acciones en masa, en este caso elimina
     */
    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ejemplo_plugin';

        //si la acción actual es delete significa que estamos eliminando
        if ('delete' === $this->current_action())
        {
            $ids = isset($_GET['id']) ? $_GET['id'] : array();
            //si es un array de ids
            if (is_array($ids))
            {
            	$ids = implode(',', $ids);
            }

            //si hay ids eliminamos
            if (!empty($ids))
            {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }


}