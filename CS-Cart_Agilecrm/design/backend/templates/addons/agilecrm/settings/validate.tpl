<fieldset>

<div id="connect_settings">

    <div class="control-group">
        <div class="controls">
            <input style="margin-left:200px" type="button" id="agile_btn" value="Validate" class="btn-primary cm-skip-avail-switch"/>
            <span id="agile_connect_message"></span>
        </div>
    </div>

    <script type="text/javascript">
     
    //<![CDATA[
    {literal}
    $(document).ready(function(){

       $("#agile_btn").click(function(){
            var agile_domain = document.getElementById("addon_option_agilecrm_agile_domain").value;
            var key = document.getElementById("addon_option_agilecrm_agile_rest_api_key").value
            var email = document.getElementById("addon_option_agilecrm_agile_email").value

            domain_regexp = /^[a-zA-Z]+$/;
            rest_api_key_regexp = /^[a-zA-Z0-9]+$/;
            email_regexp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

            if(agile_domain.length == 0||key.length == 0||email.length == 0){
                $("#agile_connect_message").text("One of the fields is empty. Please fill all the fields");
                $("#agile_connect_message").css({ "color" : "red"});
                return false;
            }
            else if(!agile_domain.match(domain_regexp)){
                $("#agile_connect_message").text("Invalid Domain");
                $("#agile_connect_message").css({ "color" : "red"});
                return false;
            }
            else if(!key.match(rest_api_key_regexp)){
                $("#agile_connect_message").text("Invalid API Key");
                $("#agile_connect_message").css({ "color" : "red"});
                return false;
            }
            else if(!email.match(email_regexp)){
                $("#agile_connect_message").text("Invalid Email");
                $("#agile_connect_message").css({ "color" : "red"});
                return false;
            }
            $.ajax({ 
                
                url : 'https://' + agile_domain + '.agilecrm.com/core/js/api/email?id=' + key + '&email=as', 
                type : 'GET', 
                dataType : 'jsonp',
                success : function(json)
                {
                    if (json.hasOwnProperty('error')){
                        $("#agile_connect_message").text("Invalid api key or domain name");
                        $("#agile_connect_message").css({ "color" : "red"})
                    }
                    else{
                        $("#agile_connect_message").text("Validation Successful");
                        $("#agile_connect_message").css({ "color" : "green"});
                    }

                    return;
                } 
            });
       })
    })
    {/literal}
        //]]>
    </script>

<!--connect_settings--></div>

</fieldset>
