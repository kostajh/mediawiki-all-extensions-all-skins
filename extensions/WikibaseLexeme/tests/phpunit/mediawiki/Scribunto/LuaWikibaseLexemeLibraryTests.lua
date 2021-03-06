local testframework = require 'Module:TestFramework'

local tests = {
	{ name = 'getLemmas of existing Lexeme',
	  func = mw.wikibase.lexeme.getLemmas,
	  args = { 'L1' },
	  expect = { { { 'English lemma', 'en' }, { 'British English lemma', 'en-gb' } } },
	},
	{ name = 'getLemmas of missing Lexeme',
	  func = mw.wikibase.lexeme.getLemmas,
	  args = { 'L1000' },
	  expect = { nil },
	},
	{ name = 'getLemmas of invalid Lexeme ID',
	  func = mw.wikibase.lexeme.getLemmas,
	  args = { 'invalid' },
	  expect = { nil },
	},
	{ name = 'getLanguage of existing Lexeme',
	  func = mw.wikibase.lexeme.getLanguage,
	  args = { 'L1' },
	  expect = { 'Q1' },
	},
	{ name = 'getLanguage of missing Lexeme',
	  func = mw.wikibase.lexeme.getLanguage,
	  args = { 'L1000' },
	  expect = { nil },
	},
	{ name = 'getLanguage of invalid Lexeme ID',
	  func = mw.wikibase.lexeme.getLanguage,
	  args = { 'invalid' },
	  expect = { nil },
	},
	{ name = 'getLexicalCategory of existing Lexeme',
	  func = mw.wikibase.lexeme.getLexicalCategory,
	  args = { 'L1' },
	  expect = { 'Q2' },
	},
	{ name = 'getLexicalCategory of missing Lexeme',
	  func = mw.wikibase.lexeme.getLexicalCategory,
	  args = { 'L1000' },
	  expect = { nil },
	},
	{ name = 'getLexicalCategory of invalid Lexeme ID',
	  func = mw.wikibase.lexeme.getLexicalCategory,
	  args = { 'invalid' },
	  expect = { nil },
	},
	{ name = 'splitLexemeId for sense ID',
	  func = mw.wikibase.lexeme.splitLexemeId,
	  args = { 'L123-S456' },
	  expect = { 'L123', 'S456' },
	},
	{ name = 'splitLexemeId for form ID',
	  func = mw.wikibase.lexeme.splitLexemeId,
	  args = { 'L123-F456' },
	  expect = { 'L123', 'F456' },
	},
	{ name = 'splitLexemeId for lexeme ID',
	  func = mw.wikibase.lexeme.splitLexemeId,
	  args = { 'L123' },
	  expect = { 'L123' },
	},
	{ name = 'splitLexemeId for item ID',
	  func = mw.wikibase.lexeme.splitLexemeId,
	  args = { 'Q123' },
	  expect = { nil },
	},
	{ name = 'splitLexemeId for sense ID with surrounding garbage',
	  func = mw.wikibase.lexeme.splitLexemeId,
	  args = { 'blah L123-S456 blah' },
	  expect = { nil },
	},
	{ name = 'splitLexemeId for form ID with surrounding garbage',
	  func = mw.wikibase.lexeme.splitLexemeId,
	  args = { 'blah L123-F456 blah' },
	  expect = { nil },
	},
	{ name = 'splitLexemeId for lexeme ID with surrounding garbage',
	  func = mw.wikibase.lexeme.splitLexemeId,
	  args = { 'blah L123 blah' },
	  expect = { nil },
	},
	{ name = 'splitLexemeId for lexeme ID with leading zeroes',
	  func = mw.wikibase.lexeme.splitLexemeId,
	  args = { 'L0123' },
	  expect = { nil },
	},
	{ name = 'splitLexemeId for sense ID with leading zeroes',
	  func = mw.wikibase.lexeme.splitLexemeId,
	  args = { 'L123-S0456' },
	  expect = { nil },
	},
	{ name = 'splitLexemeId for form ID with leading zeroes',
	  func = mw.wikibase.lexeme.splitLexemeId,
	  args = { 'L123-F0456' },
	  expect = { nil },
	},
}

return testframework.getTestProvider( tests )
