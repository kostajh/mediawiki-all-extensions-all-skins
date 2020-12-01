<?php
namespace BlueSpice\Social\Renderer;

use BlueSpice\Data\ReaderParams;
use BlueSpice\IParamProvider;
use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Renderer;
use BlueSpice\Renderer\Params;
use BlueSpice\Social\Data\Entity\Store;
use BlueSpice\Social\Entity;
use BlueSpice\Social\EntityListContext;
use BlueSpice\Social\ExtendedSearch\MappingProvider\Entity as MappingProvider;
use Config;
use Html;
use HtmlArmor;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use RequestContext;
use Sanitizer;
use Title;
use User;

class EntityList extends Renderer implements IParamProvider {
	const PARAM_CONTEXT = 'context';
	const PARAM_STORE = 'store';
	const PARAM_USER = 'user';
	const PARAM_ENTITY_LIST_RENDERER_NAME = 'renderername';

	const PARAM_SHOW_HEADLINE = 'showheadline';
	const PARAM_SHOW_ENTITY_LIST_MENU = 'showentitylistmenu';
	const PARAM_SHOW_ENTITY_SPAWNER = 'showentityspawner';
	const PARAM_USE_ENDLESS_SCROLL = 'useendlessscroll';
	const PARAM_SHOW_ENTITY_LIST_MORE = 'showentitylistmore';
	const PARAM_USE_MORE_SCROLL = 'usemorescroll';
	const PARAM_MORE_LINK = 'morelink';
	const PARAM_HEADLINE_MESSAGE_KEY = 'headlinemessagekey';
	const PARAM_OUTPUT_TYPES = 'outputtypes';
	const PARAM_PRELOAD_TITLES = 'preloadtitles';
	const PARAM_HIDDEN = 'hidden';
	const PARAM_PERSIST_SETTINGS = 'persistsettings';

	const PARAM_LIMIT = 'limit';
	const PARAM_SORT = 'sort';
	const PARAM_OFFSET = 'start';
	const PARAM_AVAILABLE_SORTER_FIELDS = 'availablesorterfields';
	const PARAM_LOCKED_OPTION_NAMES = 'lockedoptionnames';

	const PARAM_FILTER = 'filter';
	const PARAM_AVAILABLE_FILTER_FIELDS = 'availablefilterfields';
	const PARAM_LOCKED_FILTER_NAMES = 'lockedfilternames';
	const PARAM_AVAILABLE_TYPES = 'availabletypes';

	const PARAM_PRELOADED_ENTITIES = 'preloadedentities';

	const PARAM_ENTITY_LIST_DATA_ATTR = 'data-entitylist';

	/**
	 *
	 * @var EntityListContext
	 */
	protected $context = null;

	/**
	 *
	 * @var User
	 */
	protected $user = null;

	/**
	 *
	 * @var Store
	 */
	protected $store = null;

	/**
	 *
	 * @var Entity[]
	 */
	protected $entities = null;

	/**
	 *
	 * @var Params
	 */
	protected $params = null;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '' ) {
		parent::__construct( $config, $params, $linkRenderer, $context, $name );

		if ( !$this->context instanceof EntityListContext ) {
			$this->context = new EntityListContext(
				RequestContext::getMain(),
				$config,
				RequestContext::getMain()->getUser(),
				null
			);
		}
		$this->store = $params->get(
			static::PARAM_STORE,
			false
		);
		if ( !$this->store instanceof Store ) {
			$this->store = new Store();
		}
		$this->user = $params->get(
			static::PARAM_USER,
			false
		);
		if ( !$this->user instanceof User ) {
			$this->user = $this->context->getUser();
		}
		$this->params = $params;
		$this->initializeArgs();
	}

	protected function initializeArgs() {
		$this->args[static::PARAM_TAG] = 'ul';
		if ( empty( $this->args[static::PARAM_CLASS] ) ) {
			$this->args[static::PARAM_CLASS] = '';
		}
		$this->args[static::PARAM_CLASS] .= ' bs-social-entitylist';
		$nameClass = Sanitizer::escapeClass( $this->name );
		$this->args[static::PARAM_CLASS] .= " $nameClass";

		$this->args[static::PARAM_HIDDEN] = $this->params->get(
			static::PARAM_HIDDEN,
			false
		);
		if ( $this->args[static::PARAM_HIDDEN] ) {
			$this->args[static::PARAM_CLASS] .= ' initiallyhidden';
		}

		$this->args[static::PARAM_ENTITY_LIST_DATA_ATTR] = $this->params->get(
			static::PARAM_ENTITY_LIST_DATA_ATTR,
			[]
		);
		$this->args[static::PARAM_OUTPUT_TYPES] = $this->params->get(
			static::PARAM_OUTPUT_TYPES,
			[]
		);
		$this->args[ static::PARAM_OUTPUT_TYPES ] = array_merge(
			$this->context->getOutputTypes(),
			(array)$this->args[ static::PARAM_OUTPUT_TYPES ]
		);
		$this->args[static::PARAM_PRELOAD_TITLES] = $this->params->get(
			static::PARAM_PRELOAD_TITLES,
			[]
		);
		$this->args[ static::PARAM_PRELOAD_TITLES ] = array_merge(
			$this->context->getPreloadTitles(),
			(array)$this->args[ static::PARAM_PRELOAD_TITLES ]
		);

		$this->args[ static::PARAM_SHOW_ENTITY_LIST_MENU ] = $this->params->get(
			static::PARAM_SHOW_ENTITY_LIST_MENU,
			$this->context->showEntityListMenu()
		);

		$this->args[ static::PARAM_SHOW_HEADLINE ] = $this->params->get(
			static::PARAM_SHOW_HEADLINE,
			$this->context->showHeadline()
		);

		$this->args[ static::PARAM_SHOW_ENTITY_SPAWNER ] = $this->params->get(
			static::PARAM_SHOW_ENTITY_SPAWNER,
			$this->context->showEntitySpawner()
		);

		$this->args[ static::PARAM_USE_ENDLESS_SCROLL ] = $this->params->get(
			static::PARAM_USE_ENDLESS_SCROLL,
			$this->context->useEndlessScroll()
		);

		$this->args[ static::PARAM_SHOW_ENTITY_LIST_MORE ] = $this->params->get(
			static::PARAM_SHOW_ENTITY_LIST_MORE,
			$this->context->showEntityListMore()
		);

		$moreLink = $this->params->get(
			static::PARAM_MORE_LINK,
			''
		);
		$linkTarget = Title::newFromText( $moreLink );
		if ( $linkTarget ) {
			$msg = $this->msg( 'bs-social-entitylistmore-linklabel' );
			$this->args[ static::PARAM_MORE_LINK ] = MediaWikiServices::getInstance()
				->getLinkRenderer()->makeLink(
					$linkTarget,
					new HtmlArmor( $msg->text() )
			);
		} else {
			$this->args[ static::PARAM_MORE_LINK ]
				= $this->context->getMoreLink();
		}

		$this->args[ static::PARAM_HEADLINE_MESSAGE_KEY ] = $this->params->get(
			static::PARAM_HEADLINE_MESSAGE_KEY,
			$this->context->getHeadlineMessageKey()
		);

		$this->args[ static::PARAM_USE_MORE_SCROLL ] = $this->params->get(
			static::PARAM_USE_MORE_SCROLL,
			$this->context->useMoreScroll()
		);

		$this->args[ static::PARAM_LIMIT ] = $this->params->get(
			static::PARAM_LIMIT,
			$this->context->getLimit()
		);

		$this->args[ static::PARAM_ENTITY_LIST_RENDERER_NAME ]
			= $this->context->getRendererName();

		$this->args[ static::PARAM_SORT ] = $this->context->getSort();
		$paramSort = $this->params->get( static::PARAM_SORT, [] );
		if ( !empty( $paramSort ) ) {
			if ( isset( $paramSort[0]->property ) ) {
				$this->args[ static::PARAM_SORT ][0]->property
					= $paramSort[0]->property;
			}
			if ( isset( $paramSort[0]->direction ) ) {
				$this->args[ static::PARAM_SORT ][0]->direction
					= $paramSort[0]->direction;
			}
		}

		$this->args[ static::PARAM_OFFSET ] = $this->params->get(
			static::PARAM_OFFSET,
			$this->context->getStart()
		);

		$this->args[ static::PARAM_AVAILABLE_SORTER_FIELDS ] = $this->params->get(
			static::PARAM_AVAILABLE_SORTER_FIELDS,
			$this->context->getAvailableSorterFields()
		);

		$this->args[ static::PARAM_LOCKED_OPTION_NAMES ] = $this->params->get(
			static::PARAM_LOCKED_OPTION_NAMES,
			$this->context->getLockedOptionNames()
		);

		$this->args[ static::PARAM_AVAILABLE_FILTER_FIELDS ] = $this->params->get(
			static::PARAM_AVAILABLE_FILTER_FIELDS,
			$this->context->getAvailableFilterFields()
		);

		$this->args[ static::PARAM_LOCKED_FILTER_NAMES ] = $this->params->get(
			static::PARAM_LOCKED_FILTER_NAMES,
			$this->context->getLockedFilterNames()
		);

		$this->args[ static::PARAM_FILTER ] = (array)$this->params->get(
			static::PARAM_FILTER,
			(array)$this->context->getFilters()
		);
		$this->args[static::PARAM_PERSIST_SETTINGS] = $this->params->get(
			static::PARAM_PERSIST_SETTINGS,
			$this->getContext()->getPersistSettings()
		);
		$schema = $this->store->getReader( $this->context )->getSchema();
		foreach ( $this->args[ static::PARAM_FILTER ] as &$filter ) {
			$filter = (object)$filter;

			if ( !isset( $filter->field ) ) {
				unset( $filter->field );
				continue;
			}
			if ( !in_array( $filter->field, $schema->getFilterableFields() ) ) {
				unset( $filter->field );
				continue;
			}
			$mapping = MappingProvider::getValueTypeMapping();

			$type = $this->schema[$filter->field][$schema::TYPE];
			if ( isset( $mapping[$type] ) ) {
				$type = $mapping[$type];
			}
			$filter->type = $type;
		}

		$this->args[ static::PARAM_AVAILABLE_TYPES ] = $this->params->get(
			static::PARAM_AVAILABLE_TYPES,
			$this->context->getAllowedTypes()
		);

		$this->args[static::PARAM_PRELOADED_ENTITIES] = $this->params->get(
			static::PARAM_PRELOADED_ENTITIES,
			$this->context->getPreloadedEntities()
		);

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSSocialEntityListInitialized',
			[
				$this,
				&$this->args,
				$this->params
			]
		);
		foreach ( $this->args as $name => $arg ) {
			if ( $name === static::PARAM_ENTITY_LIST_DATA_ATTR ) {
				continue;
			}
			$this->args[ static::PARAM_ENTITY_LIST_DATA_ATTR ][$name] = $arg;
		}
		$this->args[ static::PARAM_ENTITY_LIST_DATA_ATTR ][ 'schema' ]
			= $this->context->getSchema();
		$this->args[ static::PARAM_ENTITY_LIST_DATA_ATTR ][ 'EntityListContext' ]
			= get_class( $this->getContext() );
		if ( $this->getContext()->getParent() instanceof Entity ) {
			$this->args[ static::PARAM_ENTITY_LIST_DATA_ATTR ]
				[ Entity::ATTR_PARENT_ID ]
				= $this->getContext()->getParent()->get( Entity::ATTR_ID );
		}
		$this->args[static::PARAM_ENTITY_LIST_DATA_ATTR][static::PARAM_PERSIST_SETTINGS] =
				$this->args[static::PARAM_PERSIST_SETTINGS];
	}

	/**
	 *
	 * @return Entity[]
	 */
	public function getEntities() {
		if ( $this->entities ) {
			return $this->entities;
		}
		$readerParams = $this->makeStoreReaderParams();
		$res = $this->store->getReader( $this->context )->read( $readerParams );
		$factory = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' );

		$this->entities = [];
		foreach ( $res->getRecords() as $record ) {
			$this->entities[] = $factory->newFromObject(
				(object)$record->getData()
			);
		}
		return $this->entities;
	}

	/**
	 * Returns a rendered template as HTML markup
	 * @return string - HTML
	 */
	public function render() {
		$content = '';
		if ( $this->args[ static::PARAM_SHOW_ENTITY_LIST_MENU ] ) {
			$content .= $this->renderEntityListMenu();
		}
		if ( $this->args[ static::PARAM_SHOW_HEADLINE ] ) {
			$content .= $this->renderEntityListHeadline();
		}
		$content .= $this->getOpenTag();
		$content .= $this->makeTagContent();
		$content .= $this->getCloseTag();
		if ( $this->args[ static::PARAM_SHOW_ENTITY_LIST_MORE ] ) {
			$content .= $this->renderEntityListMore();
		}

		return $content;
	}

	protected function makeTagContent() {
		$content = '';

		foreach ( $this->args[ static::PARAM_PRELOADED_ENTITIES ] as $raw ) {
			$content .= $this->renderPreloadedEntities( $raw );
		}
		foreach ( $this->getEntities() as $entity ) {
			$content .= $this->renderEntitiy( $entity );
		}

		return $content;
	}

	protected function renderEntityListMenu() {
		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'entitylistmenu',
			new Params( [ EntityList\Menu::PARAM_ENTITY_LIST => $this ] )
		);
		return $renderer->render();
	}

	protected function renderEntityListHeadline() {
		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'entitylistheadline',
			new Params( [ EntityList\Menu::PARAM_ENTITY_LIST => $this ] )
		);
		return $renderer->render();
	}

	protected function renderEntityListMore() {
		$limitReached = count( $this->getEntities() )
			< $this->args[ static::PARAM_LIMIT];
		if ( $limitReached && $this->args[static::PARAM_USE_MORE_SCROLL] ) {
			return '';
		}
		$renderer = MediaWikiServices::getInstance()->getService( 'BSRendererFactory' )->get(
			'entitylistmore',
			new Params( [ EntityList\Menu::PARAM_ENTITY_LIST => $this ] )
		);
		return $renderer->render();
	}

	/**
	 *
	 * @param Entity $entity
	 * @param string $out
	 * @return string
	 */
	protected function renderEntitiy( Entity $entity, $out = '' ) {
		$renderType = 'Default';

		if ( isset( $this->args[ static::PARAM_OUTPUT_TYPES ][$entity::TYPE] ) ) {
			$renderType
				= $this->args[ static::PARAM_OUTPUT_TYPES ][$entity::TYPE];
		}

		if ( !empty( $this->args[static::PARAM_PRELOAD_TITLES][$entity::TYPE] ) ) {
			$entity->set(
				$entity::ATTR_PRELOAD,
				$this->args[static::PARAM_PRELOAD_TITLES][$entity::TYPE]
			);
		}

		$out .= Html::openElement( 'li' );
		$renderer = $entity->getRenderer( $this->getContext() );
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSSocialEntityListRenderEntity',
			[
				$this,
				$entity,
				&$renderer,
				&$renderType
			]
		);
		$out .= $renderer->render( $renderType );
		$out .= Html::closeElement( 'li' );
		return $out;
	}

	/**
	 *
	 * @param \stdClass|null $rawEntity
	 * @param string $out
	 * @return string
	 */
	protected function renderPreloadedEntities( \stdClass $rawEntity = null, $out = '' ) {
		if ( !$rawEntity ) {
			return $out;
		}

		$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )->newFromObject(
			(object)$rawEntity
		);
		if ( !$entity instanceof Entity ) {
			return $out;
		}

		// if this is an existing entity we need to render one less from the
		// store, so the given limit fits ;)
		// this may be cool for pined items in the future.
		if ( $entity->exists() && $this->args[static::PARAM_LIMIT] > 1 ) {
			$this->args[static::PARAM_LIMIT] --;
		}

		return $this->renderEntitiy( $entity, $out );
	}

	protected function makeStoreReaderParams() {
		return new ReaderParams( $this->args );
	}

	protected function makeTagAttribs() {
		$attrbs = [];
		$attrbs[ static::PARAM_ENTITY_LIST_DATA_ATTR ] = \FormatJson::encode(
			$this->args[ static::PARAM_ENTITY_LIST_DATA_ATTR ]
		);
		return array_merge( $attrbs, parent::makeTagAttribs() );
	}

	/**
	 *
	 * @return \User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 *
	 * @return EntityListContext
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 *
	 * @return array
	 */
	public function getArgs() {
		return $this->args;
	}

	/**
	 * @return ParamDefinition[]
	 */
	public function getArgsDefinitions() {
		$validator = new \BSTitleValidator();
		$validator->setOptions( [ 'hastoexist' => false ] );
		return [
			new ParamDefinition(
				ParamType::BOOLEAN,
				static::PARAM_SHOW_HEADLINE,
				$this->getContext()->showHeadline()
			),
			new ParamDefinition(
				ParamType::BOOLEAN,
				static::PARAM_SHOW_ENTITY_LIST_MENU,
				$this->getContext()->showEntityListMenu()
			),
			new ParamDefinition(
				ParamType::BOOLEAN,
				static::PARAM_SHOW_ENTITY_SPAWNER,
				$this->getContext()->showEntitySpawner()
			),
			new ParamDefinition(
				ParamType::BOOLEAN,
				static::PARAM_USE_ENDLESS_SCROLL,
				$this->getContext()->useEndlessScroll()
			),
			new ParamDefinition(
				ParamType::BOOLEAN,
				static::PARAM_SHOW_ENTITY_LIST_MORE,
				$this->getContext()->showEntityListMore()
			),
			/*new ParamDefinition( problematic
				ParamType::STRING,
				static::PARAM_MORE_LINK,
				$this->getContext()->getMoreLink()
			),*/
			new ParamDefinition(
				ParamType::STRING,
				static::PARAM_HEADLINE_MESSAGE_KEY,
				$this->getContext()->getHeadlineMessageKey()
			),
			new ParamDefinition(
				'array',
				static::PARAM_OUTPUT_TYPES,
				[]
			),
			new ParamDefinition(
				'array',
				static::PARAM_PRELOAD_TITLES,
				[]
			),
			new ParamDefinition(
				ParamType::BOOLEAN,
				static::PARAM_HIDDEN,
				false
			),
			new ParamDefinition(
				ParamType::BOOLEAN,
				static::PARAM_PERSIST_SETTINGS,
				$this->getContext()->getPersistSettings()
			),
			new ParamDefinition(
				ParamType::INTEGER,
				static::PARAM_LIMIT,
				$this->context->getLimit()
			),
			new ParamDefinition(
				'array',
				static::PARAM_SORT,
				$this->context->getSort()
			),
			new ParamDefinition(
				ParamType::INTEGER,
				static::PARAM_OFFSET,
				$this->context->getStart()
			),
			new ParamDefinition(
				ParamType::STRING,
				static::PARAM_AVAILABLE_SORTER_FIELDS,
				$this->context->getAvailableSorterFields(),
				null,
				true
			),
			new ParamDefinition(
				ParamType::STRING,
				static::PARAM_LOCKED_OPTION_NAMES,
				$this->context->getLockedOptionNames(),
				null,
				true
			),
			new ParamDefinition(
				'array',
				static::PARAM_FILTER,
				(array)$this->context->getFilters()
			),
			new ParamDefinition(
				ParamType::STRING,
				static::PARAM_AVAILABLE_FILTER_FIELDS,
				$this->context->getAvailableFilterFields(),
				null,
				true
			),
			new ParamDefinition(
				ParamType::STRING,
				static::PARAM_LOCKED_FILTER_NAMES,
				$this->context->getLockedFilterNames(),
				null,
				true
			),
			new ParamDefinition(
				ParamType::STRING,
				static::PARAM_AVAILABLE_TYPES,
				$this->context->getAllowedTypes(),
				null,
				true
			),
			new ParamDefinition(
				'array',
				static::PARAM_PRELOADED_ENTITIES,
				$this->context->getPreloadedEntities()
			),
		];
	}

}
