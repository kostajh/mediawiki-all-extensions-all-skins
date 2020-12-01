/**
 * OIL_Constants.js
 * Defines certain constants used by OfflineImportLexicon scripts.
 *
 * @Author: Lia Veja
 * @Date: 08.12.2012
 * @Time: 19:33
 *
 * @License: Quadruple licensed GFDL, GPL, LGPL and Creative Commons Attribution 3.0 (CC-BY-3.0)
 * Choose whichever license of these you like best :-)
 * This file belongs to: ImportOfflineLexicon extension
 */
var jsonGeneralLink = '/Special:Ask',
	jsonLink = '/-5B-5BCategory:Lexicon-5D-5D/-3FTitle' +
                         '/searchlabel%3Dcool/limit%3D100/format%3Djson/callback=?',
	jsonLinkFile = '/@@@/-3FPropertyPage/searchlabel%3Dcool/limit%3D20/format%3Djson/callback=?',
	// var jsonLinkVolume = "/-5B-5BCategory:@@@-5D-5D-0A-5B-5B-2DHas-20Volume::%2B-5D-5D/-3FTitle/searchvolume%3Dcool/limit%3D100/format%3Djson/callback=?";
	jsonLinkVolume = '/-5B-5BCategory:@@@-5D-5D-5B-5B-2DHas-20Volume::%2B-5D-5D/-3FTitle' +
                         '/searchvolume%3Dcool/limit%3D100/format%3Djson/callback=?',
	jsonLinkArticle = '/-5B-5BPart-20of-20Volume::@@@-5D-5D/-3FTitle' +
                         '/searcharticle%3Dcool/limit%3D100/format%3Djson/callback=?',
	jsonLinkArticleNoVolume = '/-5B-5BPart-20of-20Lexicon::@@@-5D-5D-3Cq-3E-5B-5BCategory:Lemma-5D-5D-20OR-20-5B-5BCategory:Article' +
                         '-5D-5D-20OR-20-5B-5BCategory:TOC-5D-5D-20OR-20-5B-5BCategory:TitlePage-5D-5D-3C-2Fq-3E/-3FTitle' +
                         '/searcharticle%3Dcool/limit%3D100/format%3Djson/callback=?',
	// volumes from bottom-top approach {{#ask: [[Part of Lexicon::1859-78 Schmid]][[Category:Volume]] | ?Title | format = table}}
	jsonLinkVolumeB = '/-5B-5BPart-20of-20Lexicon::@@@-5D-5D-0A-5B-5B-Category:Volume-5D-5D/-3FTitle' +
                         '/searchvolumef%3Dcool/limit%3D100/format%3Djson/callback=?',
	jsonLexicon = '/-5B-5B@@@-5D-5D/-3FTitle/-3FEditor/-3FHas-20Volume/-3FIDBBF/-3FLanguage' +
                         '/-3FPlace-20of-20Publication/-3FPublisher/-3FURN/-3FYear-20of-20Publication/-3FEdition/-3FSubtitle/searchlabel%3DlexiconBx/limit%3D20/format%3Djson',
	jsonImages = '/-5B-5B@@@-5D-5D/-3FHas-20Digital-20Image/searchlabel%3Dimages/limit%3D100/format%3Djson/callback=?',
	jsonArticles = '/-5B-5BHas-20Digital-20Image::@@@-5D-5D/-3FTitle/searchlabel%3Darticles/limit%3D100/format%3Djson/callback=?';
