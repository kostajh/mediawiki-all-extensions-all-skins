<?php

namespace BlueSpice\Social\Renderer;

use BlueSpice\Context;
use BlueSpice\Renderer\Params;
use BlueSpice\Renderer\UserImage;
use BlueSpice\Social\Entity as SocialEntity;
use BlueSpice\Social\EntityListContext\Children;
use BlueSpice\Timestamp;
use BlueSpice\Utility\CacheHelper;
use Config;
use FormatJson;
use Html;
use IContextSource;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use MWException;
use RequestContext;
use User;

class Entity extends \BlueSpice\Renderer\Entity {
	const NO_TEMPLATE_CACHE = 'notemplatecache';
	const DEBUG_MODE = 'debug';

	const RENDER_TYPE = 'rendertype';

	const RENDER_TYPE_DEFAULT = 'Default';
	const RENDER_TYPE_PAGE = 'Page';
	const RENDER_TYPE_SHORT = 'Short';
	const RENDER_TYPE_LIST = 'List';

	const BEFORE_CONTENT = 'beforecontent';
	const AFTER_CONTENT = 'aftercontent';
	const USER_IMAGE = 'userimage';
	const CHILDREN = 'children';
	const HEADER = 'title';
	const AUTHOR = 'author';
	const AUTHOR_PAGE = 'authorpage';
	const ACTIONS = 'entityactions';

	protected $renderType = 'Default';
	protected $noCache = false;

	/**
	 * Constructor
	 * @param Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 * @param IContextSource|null $context
	 * @param string $name | ''
	 * @param CacheHelper|null $cacheHelper
	 */
	protected function __construct( Config $config, Params $params,
		LinkRenderer $linkRenderer = null, IContextSource $context = null,
		$name = '', CacheHelper $cacheHelper = null ) {
		parent::__construct(
			$config,
			$params,
			$linkRenderer,
			$context,
			$name,
			$cacheHelper
		);

		$this->args = array_merge(
			$this->args,
			$this->getEntity()->getFullData()
		);
		$this->args[static::USER_IMAGE] = '';
		$this->args[static::BEFORE_CONTENT] = '';
		$this->args[static::AFTER_CONTENT] = '';
		$this->args[static::PARAM_ID] = false;
		$this->args[static::CHILDREN] = '';
		$this->args[static::ACTIONS] = '';
		$owner = $this->getEntity()->getOwner();
		$userHelper = $this->getServices()->getService( 'BSUtilityFactory' )
			->getUserHelper( $owner );
		$this->args[static::AUTHOR] = $userHelper->getDisplayName();
		// TODO: Use linker - needs change in all mustache templates!
		$this->args[static::AUTHOR_PAGE] = $owner->getUserPage()->getLocalURL();
		$this->args[static::PARAM_CLASS] .= " bs-social-entity"
			. " bs-social-entity-{$this->getEntity()->get( SocialEntity::ATTR_TYPE )}";
		if ( $this->getEntity()->isArchived() ) {
			$this->args[static::PARAM_CLASS] .= ' archived';
		}
		if ( $this->isUserOwner() ) {
			$this->args[static::PARAM_CLASS] .= ' owned';
		}
		$msg = $this->getEntity()->getHeader();
		// make sure to explicitly use global context for header messages to remove
		// "self linkes" when added by parser tag i.e.
		$msg->setContext( RequestContext::getMain() );
		$this->args[static::HEADER] = $msg->parse();
	}

	/**
	 *
	 * @return string|false
	 */
	protected function getCacheKey() {
		if ( $this->noCache ) {
			return false;
		}
		return $this->getCacheHelper()->getCacheKey(
			'BlueSpiceSocial',
			'EntityRenderer',
			$this->getEntity()->get( SocialEntity::ATTR_ID ),
			$this->getEntity()->get( SocialEntity::ATTR_TIMESTAMP_TOUCHED ),
			$this->renderType,
			$this->getContext()->getUser()
		);
	}

	/**
	 *
	 * @return bool
	 */
	public function invalidate() {
		if ( !$this->getEntity()->exists() ) {
			return true;
		}
		foreach ( $this->getAvailableRenderTypes() as $renderType ) {
			$this->renderType = $renderType;
			$res = parent::invalidate();
			if ( !$res ) {
				wfDebugLog(
					'BlueSpiceSocial',
					__CLASS__ . ':' . __METHOD__ . " - '$renderType' failed"
				);
			}
		}
		return $res;
	}

	/**
	 *
	 * @param string $renderType
	 * @param bool $noCache
	 * @return string
	 * @throws MWException
	 */
	public function render( $renderType = 'Default', $noCache = false ) {
		$this->noCache = $noCache
		|| $this->getContext()->getRequest()->getBool(
			static::NO_TEMPLATE_CACHE,
			false
		)
		|| $this->getContext()->getRequest()->getBool(
			static::DEBUG_MODE,
			false
		);

		if ( !$this->isAllowedRenderType( $renderType ) ) {
			throw new MWException(
				"'$renderType' is not an allowed render type"
			);
		}
		$this->renderType = $renderType;
		$this->args[static::PARAM_CLASS]
			.= " bs-social-entity-output-$this->renderType";

		return parent::render();
	}

	/**
	 *
	 * @param string $renderType
	 * @return bool
	 */
	protected function isAllowedRenderType( $renderType ) {
		return in_array( $renderType, $this->getAvailableRenderTypes() );
	}

	/**
	 * Checks if the current user is owner of the current entity
	 * @return bool
	 */
	protected function isUserOwner() {
		$owner = $this->getEntity()->getOwner();
		$user = $this->getContext()->getUser();
		return $user
			&& $owner
			&& !$user->isAnon()
			&& !$owner->isAnon()
			&& (int)$owner->getId() === (int)$user->getId();
	}

	/**
	 *
	 * @return array
	 */
	protected function getAvailableRenderTypes() {
		return [
			static::RENDER_TYPE_DEFAULT,
			static::RENDER_TYPE_PAGE,
			static::RENDER_TYPE_SHORT,
			static::RENDER_TYPE_LIST,
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getTemplateName() {
		return $this->getEntity()->getConfig()->get( "EntityTemplate$this->renderType" );
	}

	/**
	 *
	 * @return array
	 */
	protected function makeTagAttribs() {
		$attribs = parent::makeTagAttribs();
		$attribs['data-entity'] = FormatJson::encode( $this->getEntity() );
		$attribs['data-id'] = $this->getEntity()->get( SocialEntity::ATTR_ID );
		$attribs['data-type'] = $this->getEntity()->get( SocialEntity::ATTR_TYPE );
		$attribs['data-output'] = $this->renderType;
		return $attribs;
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_userimage( $val ) {
		$factory = $this->getServices()->getService( 'BSRendererFactory' );
		$user = User::newFromId( $this->getEntity()->get(
			SocialEntity::ATTR_OWNER_ID,
			0
		) );
		$image = $factory->get( 'userimage', new Params( [
			UserImage::PARAM_USER => $user,
			UserImage::PARAM_WIDTH => 50,
			UserImage::PARAM_HEIGHT => 50,
		] ) );

		return $image->render();
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_beforecontent( $val ) {
		if ( !$this->getEntity()->exists() ) {
			return '';
		}
		$renderer = [];
		$out = '';
		$b = MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSSocialEntityOutputRenderBeforeContent',
			[
				$this,
				&$renderer,
				&$out,
				$val,
				$this->renderType,
			]
		);
		if ( !$b ) {
			return $out;
		}
		return $this->subRender( $out, $renderer );
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_aftercontent( $val ) {
		if ( !$this->getEntity()->exists() ) {
			return '';
		}
		$renderer = [];
		$out = '';
		$b = MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSSocialEntityOutputRenderAfterContent',
			[
				$this,
				&$renderer,
				&$out,
				$val,
				$this->renderType,
			]
		);
		if ( !$b ) {
			return $out;
		}
		return $this->subRender( $out, $renderer );
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_timestampcreated( $val ) {
		if ( !$val ) {
			return '';
		}
		try {
			$mwTS = new Timestamp(
				$this->args[SocialEntity::ATTR_TIMESTAMP_CREATED]
			);
		} catch ( MWException $e ) {
			$mwTS = new Timestamp();
		}
		$mwTS->offsetForUser( $this->getContext()->getUser() );
		$dateMode = $this->getContext()->getUser()->getOption(
			'bs-social-datedisplaymode'
		);
		$prefix = $this->msg( 'bs-social-renderer-timestampprefix-created' )->plain();
		if ( $dateMode === 'age' ) {
			return "$prefix " . Html::element(
				'span', [ 'class' => 'timestampcreated' ],
				$mwTS->getAgeString()
			);
		}
		$ts = $this->getContext()->getLanguage()->userTimeAndDate(
			$mwTS->format( 'YmdHis' ),
			$this->getContext()->getUser()
		);
		return "$prefix " . Html::element(
			'span', [ 'class' => 'timestampcreated' ],
			$ts
		);
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_timestamptouched( $val ) {
		if ( !$val ) {
			return '';
		}
		try {
			$mwTS = new Timestamp(
				$this->args[SocialEntity::ATTR_TIMESTAMP_TOUCHED]
			);
		} catch ( MWException $e ) {
			$mwTS = new Timestamp();
		}
		$mwTS->offsetForUser( $this->getContext()->getUser() );
		$dateMode = $this->getContext()->getUser()->getOption(
			'bs-social-datedisplaymode'
		);

		$prefix = $this->msg( 'bs-social-renderer-timestampprefix-touched' )->plain();
		if ( $dateMode === 'age' ) {
			return "$prefix " . Html::element(
				'span', [ 'class' => 'timestamptouched' ],
				$mwTS->getAgeString()
			);
		}
		$ts = $this->getContext()->getLanguage()->userTimeAndDate(
			$mwTS->format( 'YmdHis' ),
			$this->getContext()->getUser()
		);
		return "$prefix " . Html::element(
			'span', [ 'class' => 'timestamptouched' ],
			$ts
		);
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_children( $val ) {
		if ( !$this->getEntity()->exists() ) {
			return '';
		}
		if ( !$this->getEntity()->getConfig()->get( 'CanHaveChildren' ) ) {
			return '';
		}

		$renderer = $this->getChildListRenderer();
		return $renderer->render();
	}

	/**
	 *
	 * @return EntityList
	 */
	protected function getChildListRenderer() {
		return $this->getServices()->getService( 'BSRendererFactory' )->get(
			'entitylist',
			new Params( [
				EntityList::PARAM_CONTEXT => $this->getChildListContext(),
				EntityList::PARAM_HIDDEN => $this->isChildListInitiallyHidden()
			] )
		);
	}

	/**
	 *
	 * @return bool
	 */
	protected function isChildListInitiallyHidden() {
		// masterpiece!
		return $this->getEntity()->getConfig()->get(
			"EntityListInitiallyHiddenChildren$this->renderType"
		);
	}

	/**
	 *
	 * @return Children
	 */
	protected function getChildListContext() {
		$class = $this->getEntity()->getConfig()->get(
			'ChildListContextClass'
		);
		$context = new $class(
			new Context(
				$this->getContext(),
				$this->getEntity()->getConfig()
			),
			$this->getEntity()->getConfig(),
			$this->getContext()->getUser(),
			$this->getEntity()
		);
		return $context;
	}

	/**
	 *
	 * @return MediaWikiServices
	 */
	protected function getServices() {
		return MediaWikiServices::getInstance();
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_beforechildren( $val ) {
		$renderer = [];
		$out = '';
		$b = MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSSocialEntityOutputRenderBeforeChildren',
			[
				$this,
				&$renderer,
				&$out,
				$val,
				$this->renderType,
			]
		);
		if ( !$b ) {
			return $out;
		}
		return $this->subRender( $out, $renderer );
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_afterchildren( $val ) {
		$renderer = [];
		$out = '';
		$b = MediaWikiServices::getInstance()->getHookContainer()->run(
			'BSSocialEntityOutputRenderAfterChildren',
			[
				$this,
				&$renderer,
				&$out,
				$val,
				$this->renderType,
			]
		);
		if ( !$b ) {
			return $out;
		}
		return $this->subRender( $out, $renderer );
	}

	/**
	 *
	 * @param mixed $val
	 * @return string
	 */
	protected function render_entityactions( $val ) {
		$renderer = $this->getActionsRenderer();
		return $renderer->render();
	}

	/**
	 *
	 * @return EntityActions
	 */
	protected function getActionsRenderer() {
		return $this->getServices()->getService( 'BSRendererFactory' )->get(
			'entityactions',
			new Params( [
				EntityActions::PARAM_ENTITY => $this->getEntity(),
			] )
		);
	}

	/**
	 *
	 * @param string $out
	 * @param array $renderer - array of \BlueSpice\Renderer | \ViewBaseElement
	 * | strings
	 * @return string
	 */
	protected function subRender( $out, $renderer ) {
		if ( empty( $renderer ) ) {
			return $out;
		}

		foreach ( $renderer as $item ) {
			if ( is_string( $item ) ) {
				$out .= $item;
				continue;
			}
			if ( $item instanceof \BlueSpice\Renderer ) {
				$out .= $item->render();
				continue;
			}
			// backwards compatibility
			if ( $item instanceof \ViewBaseElement ) {
				$out .= $item->execute();
				continue;
			}
			$out .= (string)$item;
		}
		return $out;
	}

}
