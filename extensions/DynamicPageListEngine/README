<!-- 
     page taken from 
     https://www.mediawiki.org/wiki/Extension:DynamicPageListEngine/Description 
-->  

The '''DynamicPageListEngine''' extension is a backend providing dynamic page lists, which can be used on [[w:php|]] level for other extensions, or on [[w:Lua (programming language)|Lua]] level with the [[Extension:Scribunto|Scribunto]] extension. The page selection and sorting criteria are compatible with [[Extension:DynamicPageList (Wikimedia)|DynamicPageList (Wikimedia)]].

Given that there are already [[Extension:DynamicPageList (Wikimedia)|DynamicPageList (Wikimedia)]] and [[Extension:DynamicPageList (third-party)|DynamicPageList (third-party)]], which are not completely compatible, you might be wondering why yet another such extension. In short:
# I needed dynamic page lists on Lua level, and none of the two existing extensions provided that.
# Adding such a feature on top of [[Extension:DynamicPageList (Wikimedia)|DynamicPageList (Wikimedia)]] was not feasible due to its monolithic implementation.
# Adding such a feature on top of [[Extension:DynamicPageList (third-party)|DynamicPageList (third-party)]] might have been possible, but then it would have been partially incompatible with the Wikimedia extension. More importantly, as the  [http://semeb.com/dpldemo/index.php?title=DPL:Manual the DPL manual] states, "DPL at the moment is rather monolithic" as well.
# Hence the only solution was to create a new extension from scratch.
# The extremely high number of features in the third-party extension suggests the need for a configurable, modular and extensible backend.
