<?php
/**
 * Page post type

 * @note - It does not have settings param in constructor on purpose!
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\PostType;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Language\LanguageInterface;

final class PagePostType extends AbstractStack implements StackInterface, PostTypeInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $debugMode 	            = 0;
    private $postType               = '';

    /**
     * @param ConfigurationInterface $paramConf
     * @param LanguageInterface $paramLang
     * @param string $paramPostType
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramPostType)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // NOTE: WP core requires that post type name must be between 1 and 20 characters in length.
        $this->postType = substr(sanitize_key($paramPostType), 0, 20);
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getPostType()
    {
        return $this->postType;
    }

    /**
     * Creating a function to create our page post type
     * @param string $paramSlug
     * @param int $paramMenuPosition
     */
    public function register($paramSlug, $paramMenuPosition)
    {
        $sanitizedSlug = sanitize_key($paramSlug);
        $validMenuPosition = intval($paramMenuPosition);
        $iconURL = $this->conf->getRouting()->getAdminImagesURL('Plugin.png');

        // Set UI labels for Custom Post Type
        $labels = array(
            'name'                => $this->lang->getText('LANG_PAGE_POST_LABEL_NAME_TEXT'),
            'singular_name'       => $this->lang->getText('LANG_PAGE_POST_LABEL_SINGULAR_NAME_TEXT'),
            'menu_name'           => $this->lang->getText('LANG_PAGE_POST_LABEL_MENU_NAME_TEXT'),
            'parent_item_colon'   => $this->lang->getText('LANG_PAGE_POST_LABEL_PARENT_PAGE_COLON_TEXT'),
            'all_items'           => $this->lang->getText('LANG_PAGE_POST_LABEL_ALL_PAGES_TEXT'),
            'view_item'           => $this->lang->getText('LANG_PAGE_POST_LABEL_VIEW_PAGE_TEXT'),
            'add_new_item'        => $this->lang->getText('LANG_PAGE_POST_LABEL_ADD_NEW_PAGE_TEXT'),
            'add_new'             => $this->lang->getText('LANG_PAGE_POST_LABEL_ADD_NEW_TEXT'),
            'edit_item'           => $this->lang->getText('LANG_PAGE_POST_LABEL_EDIT_PAGE_TEXT'),
            'update_item'         => $this->lang->getText('LANG_PAGE_POST_LABEL_UPDATE_PAGE_TEXT'),
            'search_items'        => $this->lang->getText('LANG_PAGE_POST_LABEL_SEARCH_PAGES_TEXT'),
            'not_found'           => $this->lang->getText('LANG_PAGE_POST_LABEL_NOT_FOUND_TEXT'),
            'not_found_in_trash'  => $this->lang->getText('LANG_PAGE_POST_LABEL_NOT_FOUND_IN_TRASH_TEXT'),
        );

        // Set other options for Custom Post Type
        $args = array(
            'description'         => $this->lang->getText('LANG_PAGE_POST_DESCRIPTION_TEXT'),
            'labels'              => $labels,
            // Features this CPT supports in Post Editor
            /*'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),*/
            'supports'            => array( 'title', 'editor', 'author', 'thumbnail', ),
            // You can associate this CPT with a taxonomy or custom taxonomy.
            /*'taxonomies'          => array( $sanitizedSlug.'s' ),*/
            /* A hierarchical CPT is like Pages and can have
            * Parent and child items. A non-hierarchical CPT
            * is like Posts. MUST BE hierarchical=true, to have drop-downs
            */
            'hierarchical'        => true,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => $validMenuPosition,
            'menu_icon'			  => $iconURL,
            'can_export'          => true,
            'has_archive'         => false,
            'rewrite'			  => array(
                'slug' => $sanitizedSlug, // loaded from language file
                'with_front' => false,
                'pages' => false,
            ),
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            /* To manage specific rights to edit only items*/
            //'capability_type'     => $sanitizedSlug,// cant be car, because we need to
            'capability_type'     => 'page', // We allow to edit description pages for those who have rights to edit pages
            /** Note:
             * If we will set map_meta_cap = true, we will have an error:
             *   Undefined property: stdClass::$delete_posts at wp-admin/includes/class-wp-posts-list-table.php:209
             * The following code is run:
             *   if( current_user_can( $post_type_obj->cap->delete_posts ) ) {
             * The problem is that the capability, 'delete_posts', is only applied to a post type (via get_post_type_capabilities())
             * if the 'map_meta_cap' argument is set to true when you're registering the post type (via register_post_type()).
             * So, despite that we want to have here map_meta_cap = false, we have to set it to true until WordPress will fix the bug here:
             * https://core.trac.wordpress.org/ticket/30991
             */
            'map_meta_cap'        => true, // Set to false, if users are not allowed to edit/delete existing posts

            /*'capabilities' => array(
                'read'
            ),*/
            /*'capabilities' => array(
                'create_posts' => false, // Removes support for the "Add New" function - DOESN'T WORK
                'delete_post' => false,
                'publish_posts' => false,
            ),*/
        );

        // Registering your Custom Post Type
        register_post_type( $this->postType, $args );
        // flush_rewrite_rules();
    }

    /**
     * @return bool
     */
    public function deleteAllPosts()
    {
        $deleted = true;

        // Delete all NS page posts
        $posts = get_posts(array('posts_per_page' => -1, 'post_type' => $this->postType));
        foreach ($posts AS $post)
        {
            $ok = wp_delete_post( $post->ID, true);
            if($ok === false)
            {
                $deleted = false;
                $blogId = get_current_blog_id(); // This is ok here, as it might be switched, so we just grab the current
                $tableName = 'posts[post_type=&#39;'.$this->postType.'&#39;]';
                $this->errorMessages[] = sprintf($this->lang->getText('LANG_TABLE_QUERY_FAILED_FOR_WP_TABLE_DELETION_ERROR_TEXT'), $blogId, $tableName);
                if($this->debugMode)
                {
                    $debugMessage = "DELETE FAILED TO WP POSTS TABLE [post_type=".$this->postType."]";
                    $this->debugMessages[] = $debugMessage;
                    // Do not echo here, as it is used for ajax
                    //echo "<br />".$debugMessage;
                }
            }
        }

        return $deleted;
    }
}