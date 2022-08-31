This plugin provides the following features:
<ul>
<li> Enables you use a URL parameter on your home page to override the default template.<br/>
This feature is often useful when demonstrating proposed template / style changes.<br/></br>
Example useage (using the default parameter name):<br/>
https://mysite.com?bftemplatestyleid=5<br/>
</li>
<li>
Provides access to template style specific CSS and JS files (if they exist).
The names of these files will be...
<ul>
<li>media/templates/mytemplate/css/mytemplatestylealias.css</li>
<li>media/templates/mytemplate/css/mytemplatestylealias.js</li>
</ul>
The alias <i>mytemplatestyle</i> is generated from the template style title (i.e. lower case and all
non-alphabetic characters replaced with a hyphen (-)). This is follows the same rules as used for
article and menu alias generation.
</li>
</ul>
You don't have to use both features, they function independently.
