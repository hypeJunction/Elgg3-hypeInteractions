/**
 * @module page/components/interactions
 */
define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var Ajax = require('elgg/Ajax');

	var interactions = {
		ready: false,
		init: function () {
			if (interactions.ready) {
				return;
			}
			$(document).on('click', '.interactions-form:not(.elgg-state-expanded)', interactions.expandForm);
			$(document).on('click', '.interactions-state-toggler', interactions.toggleState);

			$(document).on('click', '.elgg-menu-interactions > .interactions-tab > a', interactions.triggerTabSwitch);
			$(document).on('click', '.elgg-menu-interactions > .elgg-menu-item-comments > a', interactions.triggerTabSwitch);

			$(document).on('click', '.elgg-menu-item-edit > a', interactions.loadEditForm);

			$(document).on('submit', '.interactions-form', interactions.saveComment);

			$(document).on('change', '.interactions-comments-list,.interactions-likes-list', interactions.listChanged);

			$(document).on('click', '.comment-form-toggler', interactions.toggleField);

			interactions.ready = true;
		},

		buildSelector: function (prefix, obj, suffix) {
			var selector = prefix || '';

			if (typeof obj === 'object') {
				$.each(obj, function (key, val) {
					selector += ['[', key, '=', val, ']'].join('');
				});
			}

			selector += suffix || '';

			return selector;
		},

		updateStats: function (guid, stats) {
			$.each(stats, function (trait, stat) {
				if (typeof stat.count !== 'undefined') {
					interactions.updateBadge(guid, trait, stat.count);
				}
				if (typeof stat.state !== 'undefined') {
					interactions.updateStateToggler(guid, trait, stat.state);
				}
			});
		},

		updateBadge: function (guid, trait, count) {
			if (count >= 0 && trait) {
				$(interactions.buildSelector('.interactions-badge', {
					'data-guid': guid,
					'data-trait': trait
				}, ' .interactions-badge-indicator')).text(count);
			}
		},

		updateStateToggler: function (guid, trait, state) {
			var selector = interactions.buildSelector('.interactions-state-toggler', {
				'data-guid': guid,
				'data-trait': trait
			});

			var $toggler = $(selector);

			$toggler.data({
				'state': state
			}).attr({
				'data-state': state
			}).text(elgg.echo(['interactions', trait, state].join(':')));
		},

		toggleState: function (e) {
			e.preventDefault();

			var ajax = new Ajax();

			ajax.action($(this).attr('href')).done(function (response) {
				interactions.updateStats(response.guid, response.stats);
			});
		},

		expandForm: function (e) {
			$(this).addClass('elgg-state-expanded');
			$(this).find('.elgg-input-comment').trigger('focus').focus();
		},

		saveComment: function (e) {
			e.preventDefault();

			var $form = $(this);

			var ajax = new Ajax();

			ajax.action($form.attr('action'), {
				data: ajax.objectify($form),
				beforeSend: function () {
					$form.find('[type="submit"]').prop('disabled', true);
				}
			}).done(function (response) {
				$form.siblings().find('.elgg-list')
					.first()
					.trigger(
						'addFetchedItems',
						[response.view, null, true]
					).trigger('refresh');

				$form.resetForm();

				$form.trigger('reset');

				// reset ckeditor
				$form.find('[data-cke-init]').trigger('reset');

				// Hide edit form
				if ($form.is('.interactions-form-edit')) {
					$form.siblings().replaceWith(response.view);
					$form.siblings().show();
					$form.remove();
				}

				$form.find('[type="submit"]').prop('disabled', false);
			}).fail(function () {
				$form.find('[type="submit"]').prop('disabled', false);
			});
		},

		loadEditForm: function (e) {
			e.preventDefault();
			var $elem = $(this);

			// If the menu was displayed in a popup module,
			// we need to connect it properly to the original element
			var $menu = $elem.closest('.elgg-menu');
			if ($menu.parent().is('.elgg-state-popped')) {
				$menu = $menu.parent();
			}

			var $item;

			if ($menu.is('.elgg-state-popped')) {
				var $trigger = $menu.data('trigger');

				if ($trigger.length) {
					$item = $trigger.closest('.elgg-list > li');
				}

				require(['elgg/popup'], function (popup) {
					popup.close();
				});
			}

			if (!$item) {
				$item = $menu.closest('.elgg-list > li');
			}

			if (!$item.is('.elgg-item-object-comment')) {
				return;
			}

			var ajax = new Ajax();

			ajax.path($elem.attr('href'))
				.done(function (response) {
					var $form = $(response);

					$item.append($form);

					$form.trigger('initialize');

					$form.siblings().hide();

					$form.find('textrea,input[type="text"]').first().focus().trigger('click');

					$form.addClass('elgg-form-edit');

					$form.find('.elgg-button-cancel').on('click', function (e) {
						e.preventDefault();

						$form.siblings().show();
						$form.remove();
					});
				});
		},

		triggerTabSwitch: function (e) {
			e.preventDefault();

			var $elem = $(this);

			var trait = $elem.data('trait');

			if ($elem.closest('.interactions-controls').find('.elgg-menu-interactions').find('.interactions-tab > a[data-trait="' + trait + '"]').length) {
				$elem = $elem.closest('.interactions-controls').find('.elgg-menu-interactions').find('.interactions-tab > a[data-trait="' + trait + '"]');
			}

			$elem.parent().addClass('elgg-state-selected').siblings().removeClass('elgg-state-selected');

			var $controls = $(this).closest('.interactions-controls');
			$controls.parent().addClass('interactions-has-active-tab');

			var $components = $controls.nextAll('.interactions-component');
			$components.removeClass('elgg-state-selected');

			var $traitComponent = $components.filter(interactions.buildSelector('.interactions-component', {
				'data-trait': trait
			}));

			if ($traitComponent.length) {
				$traitComponent.addClass('elgg-state-selected');
				$traitComponent.children('.interactions-form').show().find('.elgg-input-comment').trigger('focus').focus();
			} else {
				$traitComponent = $('<div></div>').addClass('interactions-component elgg-state-selected').data('trait', trait).attr('data-trait', trait);
				$traitComponent.append($('<div />').addClass('elgg-ajax-loader'));
				$controls.after($traitComponent);

				var ajax = new Ajax(false);
				ajax.path($elem.attr('href')).done(function (response) {
					$traitComponent.find('.elgg-ajax-loader').remove();
					$traitComponent.html(response);
					$traitComponent.find('.elgg-list').trigger('refresh');
					$traitComponent.children('.interactions-form').show().find('.elgg-input-comment').trigger('focus').focus();
				});
			}
		},

		listChanged: function (e, params) {
			if (params && params.guid && params.trait) {
				interactions.updateBadge(params.guid, params.trait, params.count || 0);
			}
		},

		toggleField: function (e) {
			var $elem = $(this);
			var target = $elem.data('target');

			$elem.closest('form').find(target).removeClass('hidden');
			$elem.parent('li').remove();
		}
	};

	interactions.init();

	return interactions;
});