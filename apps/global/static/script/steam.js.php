<?php $content_type = 'application/javascript' ?>
var Steam = new function()
{
    this.base_uri   = "<?php print \Steam::uri('/') ?>"
    this.static_uri = "<?php print \Steam\StaticResource::real_uri('/') ?>"
    
    this.create   = function (resource, data, callback) { return this.request("create",   resource, data, callback) }
    this.retrieve = function (resource, data, callback) { return this.request("retrieve", resource, data, callback) }
    this.update   = function (resource, data, callback) { return this.request("update",   resource, data, callback) }
    this.delete   = function (resource, data, callback) { return this.request("delete",   resource, data, callback) }
    
    this.request  = function (method, resource, data, callback)
    {
        switch (method)
        {
            case "create":
                method = "POST"
                break
            case "update":
                method = "PUT"
                break
            case "delete":
                method = "DELETE"
                break
            default:
                method = "GET"
                break
        }
        
        if (typeof data == "function")
        {
            callback = data
            data = null
        }
        else
        {
            if (method == "GET" || method == "DELETE")
            {
                for (x in data)
                    if (typeof data[x] == "undefined") delete data[x]
                
                resource += ((resource.indexOf("?") == -1) ? "?" : "&" ) + $.param(data)
                data = null
            }
            else if (method == "POST" || method == "PUT")
            {
                processData = false
                
                var xml = ""
                
                xml  = "<?php print '<?xml' ?> version=\"1.0\" encoding=\"utf-8\"?>" +
                       "<data><items>"
                
                xml += this.xml_element("item", data)
                
                xml += "</items></data>"
                
                data = xml
            }
        }
        
        $.ajax({
            type: method,
            url: this.base_uri + "models/" + resource,
            processData: false,
            data: data,
            dataType: "xml",
            complete: function(request, status)
            {
                if (typeof callback == "function")
                {
                    callback(request, status)
                }
            }
        })
    }
    
    this.xml_element = function (name, value, tags)
    {
        var xml = "";
        
        if (typeof value == "object")
        {
            for (var x in value)
            {
                if (+x == x)
                {
                    xml += this.xml_element(name, value[x], false);
                }
                else
                {
                    xml += this.xml_element(x, value[x]);
                }
            }
        }
        else if (typeof value == "string")
        {
            xml += value.replace("&", "&amp;").replace("\"", "&quot;").replace("<", "&lt;").replace(">", "&gt;");
        }
        else if (typeof value =="number")
        {
            xml += value;
        }
        
        if (tags === false)
        {
            return xml;
        }
        else
        {
            return "<" + name + ">" + xml + "</" + name + ">";
        }
    }
}
