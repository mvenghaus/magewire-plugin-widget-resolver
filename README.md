# Magewire Plugin - Widget Resolver

Innately magewire doesn't work with widgets where the content is defined in the database like cms pages, block, descriptions, etc..  This plugin adds the functionality to make it work.

### Defining the magewire component

The usual way to define a magewire component is using the layout xml. But when the content is defined in the database and the block is created on the fly you don't have any layout xml file.

To make it work you add the magewire definition to the widget xml file as parameter.

```
<widget id="test" class="Vendor\Module\Block\Widget\Test">
    ...
    <parameters>
        <parameter name="magewire" xsi:type="text" required="false" visible="false">
            <value>Vendor\Module\MagewireTest</value>
        </parameter>
    ....
```