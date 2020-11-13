The present version is an alpha-version! Testing ist still performed.

# Controlled Documents Plugin (for dw2pdf)

This plugin supports the creation of templates for dw2pdf which are intended to be layouted as controlled documents.

It implements a set of dw2pdf-replacements which can be manually or automatically filled. It also allows the definition of two self-defined replacements via the configuration.

Further details on using this plugin will be added soon. It is incomplete right now.

## Basic usage

In general, this plugin enables the usage of certain replace variable and fixed replacements in dw2pdf-templates.

Using the `<control>...</control>`-tags, it is possible to define any replacement wanted. Per line, one replacement can be defined in form of `Replacement-Name:Value` 
so that `@REPLACEMENT-NAME@` can then be used in the template and the `Value` is printed.

Example:
```
<control>
Name:John
Age:45
</control>
```
In this example, the tags `@NAME@` and `@AGE@` can be used in a template. When defined in different pages, the values are printed in the pdf.

In addition the values which are generated by the approve-plugin can be overwritten when defined within the `<control>`-tags. For example, 
you could define `Author-mark:Someone` which would overweite the author who actually marked the document using the approve plugin.

### Additional fixed replacements

* `@LASTAUTHOR@` - The last editor of the current page

### Integrating the approve-plugin

If installed, the data from the approve-plugin can be integrated in any template using the following tags:
```
@AUTHOR-MARK@ = User who clicked on "mark ready by approval"
@DATE-MARK@   = Date at which the document was marked ready for approval

@AUTHOR-APPROVE@ = User who approved the document
@DATE-APPROVE@   = Date on which the document was approved

@REVISION@ = Approve Version. Set to "draft" if the version displayed is not approved.
```


## Requirements / Compatibility

Obligatory:
* [dw2pdf-plugin](https://www.dokuwiki.org/plugin:dw2pdf) / 2020-08-20 **or**
* [dw2pdf / Modified Version](https://github.com/practical-solutions/dokuwiki-plugin-dw2pdf) (which interacts with the approve plugin, see plugin documentation for more details)

Further functionality especially for automatically controlling document flows can be achieved with the approve plugin.

Recommended:
* [approve-Plugin](https://www.dokuwiki.org/plugin:approve) / 2020-09-21
* [approve-plus-Plugin](https://github.com/practical-solutions/dokuwiki-plugin-approveplus) / 2020-09-16

This plugin is compatible with DokuWiki/**Hogfather**.





