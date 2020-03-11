

/* This section of the code registers a new block, sets an icon and a category, and indicates what type of fields it'll include. */
function grassblade_blocks() {
"use strict";

const { registerBlockType } = wp.blocks;
 
const {
    RichText,
    AlignmentToolbar,
    BlockControls,
    InspectorControls,
    BlockDescription,
    ToggleControl
} = wp.editor;

const {
  ServerSideRender
} = wp.components;

registerBlockType('grassblade/xapi-content', {
  title: gb_block_data.xapi_content_title,
  icon: 'admin-settings',
  category: 'grassblade-blocks',
  description: gb_block_data.xapi_content_desc,
  attributes: {
    check_completion: {type: 'string'},
    content_id : {type: 'string'},
    className : {type: 'string'}
  },
  
/* This configures how the content field will work, and sets up the necessary elements */
  
  edit: function(props) {

    function changeContent(event) {

      props.setAttributes({content_id: event.target.value})

      var completion = $("#grassblade_xpi_content").find(':selected').attr('data-completion-tracking');
      if (completion == 'true') {
        props.setAttributes({check_completion: gb_block_data.tracking_enable})
      } else {
        props.setAttributes({check_completion: gb_block_data.tracking_disable})
      }    
    } // end of changeContent function 

      var postSelections = [];

      postSelections.push(wp.element.createElement("option", {value: "" , hidden : true }, gb_block_data.Select_Content));
      jQuery.each(gb_block_data.post_content, function( key, value ) {
        if (props.attributes.content_id && props.attributes.content_id == value.id) {
          postSelections.push(wp.element.createElement("option", {value: value.id , "data-completion-tracking" : value.completion_tracking, selected:"selected" }, value.post_title));
          //if (!props.attributes.check_completion) {
            if (value.completion_tracking) {
              props.setAttributes({check_completion: gb_block_data.tracking_enable})
            } else {
              props.setAttributes({check_completion: gb_block_data.tracking_disable})
            }
          //}
        } else {
          postSelections.push(wp.element.createElement("option", {value: value.id , "data-completion-tracking" : value.completion_tracking}, value.post_title));
        }
      });

      const selected_controls = [
        wp.element.createElement(
          InspectorControls,
          {},
          wp.element.createElement(
            "div",
            null,
            wp.element.createElement(
              "hr",
              null
            ),
            wp.element.createElement("span", {style:{fontWeight: 600, width: '100%'}}, gb_block_data.Add_to_Page + ":  ",
            wp.element.createElement("a", {href: gb_block_data.admin_url+'post-new.php?post_type=gb_xapi_content'}, gb_block_data.Add_New)),
            wp.element.createElement("br"),
            wp.element.createElement("br"),
            wp.element.createElement("select", {onChange: changeContent, style:{width:'100%'}, id: "grassblade_xpi_content"}, postSelections),
            wp.element.createElement("a", {href: gb_block_data.admin_url+'post.php?action=edit&message=1&post='+props.attributes.content_id},(props.attributes.content_id)? gb_block_data.Edit: props.attributes.content_id),
            wp.element.createElement("br"),
            wp.element.createElement("br"),
            wp.element.createElement("a", {href: gb_block_data.admin_url+'post.php?action=edit&message=1&post='+props.attributes.content_id},props.attributes.check_completion),
          ),
        ),
      ];
    return [
              selected_controls,
              wp.element.createElement(ServerSideRender, {
                  block: "grassblade/xapi-content",
                  attributes: props.attributes
              } )
            ];

  },
  save: function(props) {
    return null;
  }
})

registerBlockType('grassblade/leaderboard', {
  title: gb_block_data.leaderboard_title,
  icon: 'universal-access-alt',
  category: 'grassblade-blocks',
  description: gb_block_data.leaderboard_desc,
  attributes: {
      content_id : {type: 'string'},
      role : {type: 'string', default: "all"},
      score : {type: 'string'},
      limit : {type: 'string', default: 20},
      className : {type: 'string'}
  },
  
/* This configures how the content field will work, and sets up the necessary elements */
  
  edit: function(props) {

    var role = props.attributes.role;

      function changeContent(event) {

        props.setAttributes({content_id: event.target.value})
        
      } // end of changeContent function

      function changeRole(event) {

        var roles = jQuery(event.target).parent().children("input:checkbox:checked").map(function() {
            return this.value;
        }).get().join(",");
        props.setAttributes({ role: roles });

      } // end of changeRole function 

      function changeScore(event) {

        props.setAttributes({score: event.target.value})
        
      } // end of changeScore function 

      function setLimit(event) {

        props.setAttributes({limit: event.target.value})
        
      } // end of setLimit function 

      var postSelections = [];

      postSelections.push(wp.element.createElement("option", {value: "" , hidden : true }, gb_block_data.Select_Content));
      jQuery.each(gb_block_data.post_content, function( key, value ) {
        if (props.attributes.content_id == value.id) {
          postSelections.push(wp.element.createElement("option", {value: value.id , selected:"selected" }, value.post_title));
        } else {
          postSelections.push(wp.element.createElement("option", {value: value.id }, value.post_title));
        }
      });

      var roleSelections = [];

      roleSelections.push(wp.element.createElement("input", { onChange: changeRole,  type: "checkbox", value: "all"}),wp.element.createElement("label", null, gb_block_data.All_Role),wp.element.createElement("br"));
      jQuery.each(gb_block_data.roles, function( key, value ) {
        var roles = props.attributes.role.split(",");
        var checked = roles.indexOf(key) < 0? "":"checked";
        roleSelections.push(wp.element.createElement("input", { onChange: changeRole, type: "checkbox", value: key, checked: checked}),wp.element.createElement("label", null, value.name),wp.element.createElement("br"));
      });

      var scoreSelections = [];

      if (props.attributes.score == "score") {
        scoreSelections.push(wp.element.createElement("option", {value: "score", selected:"selected" }, gb_block_data.Score));
        scoreSelections.push(wp.element.createElement("option", { value: "percentage" }, gb_block_data.Percentage));
      } else {
        scoreSelections.push(wp.element.createElement("option", {value: "score" }, gb_block_data.Score));
        scoreSelections.push(wp.element.createElement("option", { value: "percentage" , selected:"selected"}, gb_block_data.Percentage));
      }

      const controls = [
        wp.element.createElement(
          InspectorControls,
          {},
          wp.element.createElement(
            "div",
            null,
            wp.element.createElement(
              "hr",
              null
            ),
            wp.element.createElement("span", {style:{fontWeight: 600, width: '100%'}},gb_block_data.Content+":"),
            wp.element.createElement("select", {onChange: changeContent, style:{width:'100%'}}, postSelections),
            wp.element.createElement("br"),
            wp.element.createElement("br"),
            wp.element.createElement("span", {style:{fontWeight: 600, width: '100%'}},gb_block_data.Role+":"),
            wp.element.createElement("br"),
            wp.element.createElement("span", {style:{width: '100%'}}, "("+gb_block_data.Role_Desc+")"),
            wp.element.createElement("br"),
            wp.element.createElement("div", null, roleSelections),
            wp.element.createElement("br"),
            wp.element.createElement("span", {style:{fontWeight: 600, width: '100%'}},gb_block_data.Score_Type+":"),
            wp.element.createElement("select", {onChange: changeScore , style:{width:'100%'}}, scoreSelections),
            wp.element.createElement("br"),
            wp.element.createElement("br"),
            wp.element.createElement("span", {style:{fontWeight: 600, width: '100%'}},gb_block_data.Limit+":"),
            wp.element.createElement("input", {onChange: setLimit, style:{width:'100%'}, type: "text", value : props.attributes.limit }),
          ),
        ),
      ];

      return [
              controls,
              wp.element.createElement(ServerSideRender, {
                  block: "grassblade/leaderboard",
                  attributes: props.attributes
              } )
            ];

  },
  save: function(props) {
    return null;
  }
})


registerBlockType('grassblade/userscore', {
  title: gb_block_data.userscore_title,
  icon: 'businessman',
  category: 'grassblade-blocks',
  description: gb_block_data.userscore_desc,
  attributes: {
      content_id : {type: 'string' , default: ""},
      show : {type: 'string', default: 'total_score'},
      add : {type: 'string' , default: ""},
      label : {type: 'string', default: gb_block_data.User_Score},
      className : {type: 'string'}
  },
  
/* This configures how the content field will work, and sets up the necessary elements */
  
  edit: function(props) {

      function changeContent(event) {

        props.setAttributes({content_id: event.target.value})
        
      } // end of changeContent function

      function changeShow(event) {

        props.setAttributes({show: event.target.value})
        
      } // end of changeShow function 

      function changeAdd(event) {

        props.setAttributes({add: event.target.value})
        
      } // end of changeAdd function 

      function setLabel(event) {

        props.setAttributes({label: event.target.value})
        
      } // end of setLabel function 

      var postSelections = [];

      postSelections.push(wp.element.createElement("option", {value: "" , hidden : true }, gb_block_data.Select_Content));
      postSelections.push(wp.element.createElement("option", {value: "" }, gb_block_data.All_Content));
      jQuery.each(gb_block_data.post_content, function( key, value ) {
        if (props.attributes.content_id == value.id) {
          postSelections.push(wp.element.createElement("option", {value: value.id , selected:"selected" }, value.post_title));
        } else {
          postSelections.push(wp.element.createElement("option", {value: value.id }, value.post_title));
        }
      });

      var scoreSelections = [];

      if (props.attributes.show == "total_score") {
          scoreSelections.push(wp.element.createElement("option", { value: "" }, gb_block_data.No_Selection));
          scoreSelections.push(wp.element.createElement("option", { value: "total_score", selected:"selected" }, gb_block_data.Total_Score));
          scoreSelections.push(wp.element.createElement("option", { value: "average_percentage" }, gb_block_data.Average_Percentage));
        } else if (props.attributes.show == "average_percentage") {
          scoreSelections.push(wp.element.createElement("option", { value: "" }, gb_block_data.No_Selection));
          scoreSelections.push(wp.element.createElement("option", { value: "total_score"}, gb_block_data.Total_Score));
          scoreSelections.push(wp.element.createElement("option", { value: "average_percentage" , selected:"selected" }, gb_block_data.Average_Percentage));
        } else {
          scoreSelections.push(wp.element.createElement("option", { value: "" , selected:"selected" }, gb_block_data.No_Selection));
          scoreSelections.push(wp.element.createElement("option", { value: "total_score"}, gb_block_data.Total_Score));
          scoreSelections.push(wp.element.createElement("option", { value: "average_percentage" }, gb_block_data.Average_Percentage));
        }

        var addSelections = [];

        if (props.attributes.add == "badgeos_points") {
          addSelections.push(wp.element.createElement("option", { value: "" }, gb_block_data.No_Selection));
          addSelections.push(wp.element.createElement("option", {value: "badgeos_points", selected:"selected" }, gb_block_data.Badgeos_Points));
        } else {
          addSelections.push(wp.element.createElement("option", { value: "" , selected:"selected"}, gb_block_data.No_Selection));
          addSelections.push(wp.element.createElement("option", { value: "badgeos_points" }, gb_block_data.Badgeos_Points));
        } 
      
      const controls = [
        wp.element.createElement(
          InspectorControls,
          {},
          wp.element.createElement(
            "div",
            null,
            wp.element.createElement(
              "hr",
              null
            ),
            wp.element.createElement("span", {style:{fontWeight: 600, width: '100%'}}, gb_block_data.Label+":"),
            wp.element.createElement("input", {onChange: setLabel, type: "text", style:{width:'100%'}, value : props.attributes.label }),
            wp.element.createElement("br"),
            wp.element.createElement("br"),
            wp.element.createElement("span", {style:{fontWeight: 600, width: '100%'}},gb_block_data.xAPI_Content+":"),
            wp.element.createElement("select", {onChange: changeContent, style:{width:'100%'}}, postSelections),
            wp.element.createElement("br"),
            wp.element.createElement("br"),
            wp.element.createElement("span", {style:{fontWeight: 600, width: '100%'}},gb_block_data.Score+":"),
            wp.element.createElement("select", {onChange: changeShow, style:{width:'100%'}}, scoreSelections),
            wp.element.createElement("br"),
            wp.element.createElement("br"),
            wp.element.createElement("span", {style:{fontWeight: 600, width: '100%'}},gb_block_data.Add+":"),
            wp.element.createElement("select", {onChange: changeAdd, style:{width:'100%'}}, addSelections),
            
            wp.element.createElement("br"),
            wp.element.createElement("br"),
            wp.element.createElement("label", null , wp.element.createElement("b", null, gb_block_data.add_shortcode_desc+":")),
            wp.element.createElement("br"),
            wp.element.createElement("textarea", {rows: "5",cols: "30",value: " [grassblade_user_score " + ((props.attributes.content_id == "")? "":(" content_id=" + props.attributes.content_id)) + ((props.attributes.show == "")? "":(" show='" + props.attributes.show + "'")) + ((props.attributes.add == "")? "":(" add='" + props.attributes.add + "'" )) + "]"}),
          ),
        ),
      ];

      return [
              controls,
              wp.element.createElement(ServerSideRender, {
                  block: "grassblade/userscore",
                  attributes: props.attributes
              } )
            ];

  },
  save: function(props) {
    return null;
  } 
}) // end registerBlockType('grassblade/leaderboard' ...

registerBlockType('grassblade/user-report', {
  title: gb_block_data.user_report_title,
  icon: 'id-alt',
  category: 'grassblade-blocks',
  description: gb_block_data.user_report_desc,
  attributes: {
      bg_color : {type: 'string', default: '#83BA39'},
      className : {type: 'string'}
  },

/* This configures how the content field will work, and sets up the necessary elements */

  edit: function(props) {

      function setBG_color(value) {

        props.setAttributes({bg_color: value.hex})

      } // end of setBG_color function

      const controls = [
        wp.element.createElement(
          InspectorControls,
          {},
          wp.element.createElement(
            "div",
            null,
            wp.element.createElement(
              "hr",
              null
            ),
            wp.element.createElement("span", {style:{fontWeight: 600, width: '100%'}},gb_block_data.BG_color+":"),
            wp.element.createElement("br"),
            wp.element.createElement("br"),
            wp.element.createElement(wp.components.ColorPicker, { color: props.attributes.bg_color, onChangeComplete: setBG_color }),
            wp.element.createElement("br"),
            wp.element.createElement("br"),
            wp.element.createElement("label", null , wp.element.createElement("b", null, gb_block_data.add_shortcode_desc+":")),
            wp.element.createElement("br"),
            wp.element.createElement("textarea", {rows: "5",cols: "30",value: " [gb_user_report bg_color=" + props.attributes.bg_color + "]"}),
          ),
        ),
      ];

      return [
              controls,
              wp.element.createElement(ServerSideRender, {
                  block: "grassblade/user-report",
                  attributes: props.attributes
              } )
            ];

  },
  save: function(props) {
    return null;
  }
}) // end registerBlockType('grassblade/xapi-profile' ...


} // end grassblade_blocks();
grassblade_blocks();