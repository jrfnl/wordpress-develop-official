	public $show_errors = false;
	public $suppress_errors = false;
	public $last_error = '';
	public $num_queries = 0;
	public $num_rows = 0;
	public $rows_affected = 0;
	public $insert_id = 0;
	public $last_query;
	public $last_result;
	public $queries;
	public $prefix = '';
	public $base_prefix;
	public $ready = false;
	public $blogid = 0;
	public $siteid = 0;
	public $tables = array( ... );
	public $old_tables = array( 'categories', 'post2cat', 'link2cat' );
	public $global_tables = array( 'users', 'usermeta' );
	public $ms_global_tables = array( ... );
	public $comments;
	public $commentmeta;
	public $links;
	public $options;
	public $postmeta;
	public $posts;
	public $terms;
	public $term_relationships;
	public $term_taxonomy;
	public $termmeta;
	public $usermeta;
	public $users;
	public $blogs;
	public $blogmeta;
	public $registration_log;
	public $signups;
	public $site;
	public $sitecategories;
	public $sitemeta;
	public $field_types = array();
	public $charset;
	public $collate;
	public $func_call;
	public $is_mysql = null;
	public $time_start = null;
	public $error = null;


	// @since 0.71
	protected $result;
	protected $col_info;
	protected $dbh;

	// @since 2.9.0
	protected $dbuser;

	// @since 3.1.0
	protected $dbpassword;
	protected $dbname;
	protected $dbhost;

	// @since 3.9.0
	protected $reconnect_retries = 5;
	protected $incompatible_modes = array( ... );

	// @since 3.9.0
	private $use_mysqli = false;
	private $has_connected = false;

	// @since 4.2.0
	protected $col_meta = array();
	protected $table_charset = array();
	protected $check_current_query = true;

	// @since 4.2.0
	private $checking_collation = false;

	// @since 6.1.0
	private $allow_unsafe_unquoted_parameters = true;


