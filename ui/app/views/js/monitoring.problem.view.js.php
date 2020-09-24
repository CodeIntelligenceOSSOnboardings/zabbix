<?php
/*
** Zabbix
** Copyright (C) 2001-2020 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * @var CView $this
 */

if (array_key_exists('filter_options', $data)) { ?>
	<script type="text/javascript">
	$(function() {
		var filter = new CTabFilter($('#monitoring_problem_filter')[0], <?= json_encode($data['filter_options']) ?>),
				refresh_interval = <?= $data['refresh_interval'] ?>,
				refresh_url = '<?= $data['refresh_url'] ?>',
				refresh_timer,
				global_timerange = {
					from: filter._timeselector._data.from,
					to: filter._timeselector._data.to
				};

		filter.on(TABFILTER_EVENT_URLSET, () => {
			let url = new Curl();

			url.setArgument('action', 'problem.view.csv');
			$('#export_csv').attr('data-url', url.getUrl());

			refreshResults();
		});

		function refreshResults() {
			let url = new Curl(),
				data = $.extend(<?= json_encode($data['filter_defaults']) ?>,
					global_timerange, url.getArgumentsObject()
				);

			data.inventory = data.inventory
				? data.inventory.filter(inventory => 'value' in inventory && inventory.value !== '')
				: data.inventory;
			data.tags = data.tags
				? data.tags.filter(tag => 'tag' in tag && tag.tag !== '' && tag.value !== '')
				: data.tags;
			data.severities = data.severities
				? data.severities.filter((value, key) => value == key)
				: data.severities;

			// Modify filter data of flickerfreeScreen object with id 'problem'.
			if (data.page > 1) {
				window.flickerfreeScreen.screens['problem'].page = data.page;
			}

			window.flickerfreeScreen.screens['problem'].data.filter = data;
			window.flickerfreeScreen.screens['problem'].data.sort = data.sort;
			window.flickerfreeScreen.screens['problem'].data.sortorder = data.sortorder;
			window.flickerfreeScreen.screens['problem'].timeline.from = data.from;
			window.flickerfreeScreen.screens['problem'].timeline.to = data.to;
			window.flickerfreeScreen.refresh('problem');
		}

		function refreshCounters() {
			clearTimeout(refresh_timer);

			fetch(refresh_url, {
				method: 'POST',
				body: new URLSearchParams({filter_counters: 1})
			})
				.then(response => response.json())
				.then(response => {
					if (response.filter_counters) {
						filter.updateCounters(response.filter_counters);
					}

					refresh_timer = setTimeout(refreshCounters, refresh_interval);
				});
		}

		refreshCounters();

		// Keep timeselector changes in global_timerange.
		$.subscribe('timeselector.rangeupdate', (e, data) => {
			if (data.idx === '<?= CControllerProblem::FILTER_IDX ?>') {
				global_timerange.from = data.from;
				global_timerange.to = data.to;
			}
		});
	});
	</script>
<?php
}
?>
<script type="text/javascript">
	jQuery(function($) {
		$(document).on({
			mouseenter: function() {
				if ($(this)[0].scrollWidth > $(this)[0].offsetWidth) {
					$(this).attr({title: $(this).text()});
				}
			},
			mouseleave: function() {
				if ($(this).is('[title]')) {
					$(this).removeAttr('title');
				}
			}
		}, 'table.<?= ZBX_STYLE_COMPACT_VIEW ?> a.<?= ZBX_STYLE_LINK_ACTION ?>');

		$.subscribe('acknowledge.create', function(event, response) {
			// Clear all selected checkboxes in Monitoring->Problems.
			if (chkbxRange.prefix === 'problem') {
				chkbxRange.checkObjectAll(chkbxRange.pageGoName, false);
				chkbxRange.clearSelectedOnFilterChange();
			}

			window.flickerfreeScreen.refresh('problem');

			clearMessages();
			addMessage(makeMessageBox('good', [], response.message, true, false));
		});

		$(document).on('submit', '#problem_form', function(e) {
			e.preventDefault();

			var eventids = $('[id^="eventids_"]:checked', $(this)).map(function() {
					return $(this).val();
				}).get();

			acknowledgePopUp({eventids: eventids}, this);
		});
	});
</script>
