<?php
/*
** Zabbix
** Copyright (C) 2001-2022 Zabbix SIA
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

require_once dirname(__FILE__).'/../include/CWebTest.php';

/**
 * @backup profiles, module, services
 *
 * @onBefore prepareServiceData
 * @onBefore getCurrentZabbixVersion
 */
class testDocumentationLinks extends CWebTest {

	public function prepareServiceData() {
		CDataHelper::call('service.create', [
			[
				'name' => 'Service_1',
				'algorithm' => 1,
				'sortorder' => 1
			]
		]);
	}

	public function getCurrentZabbixVersion() {
		self::$version = substr(ZABBIX_VERSION,0,3);
	}

	/**
	 * Major version of Zabbix the test is executed on.
	 */
	private static $version;

	/**
	 * Static start of each documentation link.
	 */
	private static $path_start = 'https://www.zabbix.com/documentation/';

	public static function getGeneralDocumentationLinkData() {
		return [
			// #0 Dashboard list.
			[
				[
					'url' => 'zabbix.php?action=dashboard.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/dashboard'
				]
			],
			// #1 Certain dashboard in view mode.
			[
				[
					'url' => 'zabbix.php?action=dashboard.view&dashboardid=1',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/dashboard'
				]
			],
			// #2 Create dashboard popup.
			[
				[
					'url' => 'zabbix.php?action=dashboard.list',
					'open_button' => 'button:Create dashboard',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/dashboard#creating-a-dashboard',
					'cancel_locator' => 'id:dashboard-cancel',
					'alert' => true
				]
			],
			// #3 Widget edit form.
			[
				[
					'url' => 'zabbix.php?action=dashboard.view&dashboardid=1',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/dashboard/widgets',
					'open_button' => 'xpath:(//button[@class="btn-widget-edit"])[1]',
					'cancel_locator' => 'id:dashboard-cancel'
				]
			],
			// TODO: Uncomment this test case and adjust documentation link when ZBX-20773 is merged.
//			// #4 Add dashboard page configuration popup.
//			[
//				[
//					'url' => 'zabbix.php?action=dashboard.view&dashboardid=1',
//					'doc_link' => '/en/manual/config/reports#configuration',
//					'open_button' => 'button:Edit dashboard',
//					'second_open_button' => 'xpath://button[@id="dashboard-add"]',
//					'select_option' => 'xpath://a[text()="Add page"]',
//					'cancel_locator' => 'id:dashboard-cancel'
//				]
//			],
//			// #5 Edit dashboard sharing configuration popup.
//			[
//				[
//					'url' => 'zabbix.php?action=dashboard.view&dashboardid=1',
//					'doc_link' => '/en/manual/config/reports#configuration',
//					'open_button' => 'id:dashboard-actions',
//					'select_option' => 'xpath://a[text()="Sharing"]'
//				]
//			],
			// #6 Global search view.
			[
				[
					'url' => 'zabbix.php?action=search&search=zabbix',
					'doc_link' => '/en/manual/web_interface/global_search'
				]
			],
			// #7 Problems view.
			[
				[
					'url' => 'zabbix.php?action=problem.view',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/problems'
				]
			],
			// #8 Event details view.
			[
				[
					'url' => 'tr_events.php?triggerid=100028&eventid=95',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/problems#viewing-details'
				]
			],
			// #9 Problems Mass update popup.
			[
				[
					'url' => 'zabbix.php?action=problem.view',
					'open_button' => 'button:Mass update',
					'doc_link' => '/en/manual/acknowledges#updating-problems'
				]
			],
			// #10 Problems acknowledge popup.
			[
				[
					'url' => 'zabbix.php?action=problem.view',
					'open_button' => 'link:No',
					'doc_link' => '/en/manual/acknowledges#updating-problems'
				]
			],
			// #11 Monitoring -> Hosts view.
			[
				[
					'url' => 'zabbix.php?action=host.view',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/hosts'
				]
			],
			// #12 Create host popup in Monitoring -> Hosts view.
			[
				[
					'url' => 'zabbix.php?action=host.view',
					'open_button' => 'button:Create host',
					'doc_link' => '/en/manual/config/hosts/host#configuration'
				]
			],
			// #13 Monitoring -> Graphs view.
			[
				[
					'url' => 'zabbix.php?action=charts.view',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/hosts/graphs'
				]
			],
			// #14 Monitoring -> Web monitoring view.
			[
				[
					'url' => 'zabbix.php?action=web.view',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/hosts/web'
				]
			],
			// #15 Monitoring -> Host dashboards view (dashboards of Zabbix server host).
			[
				[
					'url' => 'zabbix.php?action=host.dashboard.view&hostid=10084',
					'doc_link' => '/en/manual/config/visualization/host_screens'
				]
			],
			// #16 Latest data view.
			[
				[
					'url' => 'zabbix.php?action=latest.view',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/latest_data'
				]
			],
			// TODO: Uncomment this test case and adjust documentation link when ZBX-20773 is merged.
//			// #17 Speccific item graph from latest data view.
//			[
//				[
//					'url' => 'history.php?action=showgraph&itemids%5B%5D=42237',
//					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/latest_data'
//				]
//			],
//			// #18 Specific item history from latest data view.
//			[
//				[
//					'url' => 'history.php?action=showvalues&itemids%5B%5D=42242',
//					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/latest_data'
//				]
//			],
			// #19 Maps list view.
			[
				[
					'url' => 'sysmaps.php',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/maps'
				]
			],
			// #20 Create map form.
			[
				[
					'url' => 'sysmaps.php?form=Create+map',
					'doc_link' => '/en/manual/config/visualization/maps/map#creating-a-map'
				]
			],
			// #21 Map import popup.
			[
				[
					'url' => 'sysmaps.php',
					'open_button' => 'button:Import',
					'doc_link' => '/en/manual/xml_export_import/maps#importing'
				]
			],
			// #22 View map view.
			[
				[
					'url' => 'zabbix.php?action=map.view&sysmapid=1',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/maps#viewing-maps'
				]
			],
			// #23 Edit map view.
			[
				[
					'url' => 'sysmap.php?sysmapid=1',
					'doc_link' => '/en/manual/config/visualization/maps/map#overview'
				]
			],

			// #24 Monitoring -> Discovery view.
			[
				[
					'url' => 'zabbix.php?action=discovery.view',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/discovery'
				]
			],
			// #25 Monitoring -> Services in view mode.
			[
				[
					'url' => 'zabbix.php?action=service.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/services/service#viewing-services'
				]
			],
			// #26 Monitoring -> Services in edit mode.
			[
				[
					'url' => 'zabbix.php?action=service.list.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/services/service#editing-services'
				]
			],
			// #27 Service configuration form popup.
			[
				[
					'url' => 'zabbix.php?action=service.list.edit',
					'open_button' => 'button:Create service',
					'doc_link' => '/en/manual/web_interface/frontend_sections/services/service#service-configuration'
				]
			],
			// #28 Service mass update popup.
			[
				[
					'url' => 'zabbix.php?action=service.list.edit',
					'open_button' => 'button:Mass update',
					'doc_link' => '/en/manual/web_interface/frontend_sections/services/service#editing-services'
				]
			],
			// #29 List of service actions.
			[
				[
					'url' => 'actionconf.php?eventsource=4',
					'doc_link' => '/en/manual/web_interface/frontend_sections/services/service_actions'
				]
			],
			// #30 Service action configuration form.
			[
				[
					'url' => 'actionconf.php?eventsource=4&form=Create+action',
					'doc_link' => '/en/manual/config/notifications/action#configuring-an-action'
				]
			],
			// #31 SLA list view.
			[
				[
					'url' => 'zabbix.php?action=sla.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/services/sla#overview'
				]
			],
			// #32 SLA create form popup.
			[
				[
					'url' => 'zabbix.php?action=sla.list',
					'open_button' => 'button:Create SLA',
					'doc_link' => '/en/manual/web_interface/frontend_sections/services/sla#configuration'
				]
			],
			// #33 SLA report view.
			[
				[
					'url' => 'zabbix.php?action=slareport.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/services/sla_report#overview'
				]
			],
			// #34 Inventory overview view.
			[
				[
					'url' => 'hostinventoriesoverview.php',
					'doc_link' => '/en/manual/web_interface/frontend_sections/inventory/overview'
				]
			],
			// #35 Inventory hosts view.
			[
				[
					'url' => 'hostinventories.php',
					'doc_link' => '/en/manual/web_interface/frontend_sections/inventory/hosts'
				]
			],
			// #36 System information report view.
			[
				[
					'url' => 'zabbix.php?action=report.status',
					'doc_link' => '/en/manual/web_interface/frontend_sections/reports/status_of_zabbix'
				]
			],
			// #37 Scheduled reports list view.
			[
				[
					'url' => 'zabbix.php?action=scheduledreport.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/reports/scheduled'
				]
			],
			// #38 Scheduled report configuration form.
			[
				[
					'url' => 'zabbix.php?action=scheduledreport.edit',
					'doc_link' => '/en/manual/config/reports#configuration'
				]
			],
			// TODO: Uncomment this test case and adjust documentation link when ZBX-20773 is merged.
//			// #39 Add scheduled report configuration popup from Dashboard view.
//			[
//				[
//					'url' => 'zabbix.php?action=dashboard.view&dashboardid=1',
//					'doc_link' => '/en/manual/config/reports#configuration',
//					'open_button' => 'xpath://button[@class="btn-action"]',
//					'select_option' => 'xpath://a[text()="Create new report"]'
//				]
//			],
			// #40 Availability report view.
			[
				[
					'url' => 'report2.php',
					'doc_link' => '/en/manual/web_interface/frontend_sections/reports/availability'
				]
			],
			// #41 Triggers top 100 report view.
			[
				[
					'url' => 'toptriggers.php',
					'doc_link' => '/en/manual/web_interface/frontend_sections/reports/triggers_top'
				]
			],
			// #42 Audit log view.
			[
				[
					'url' => 'zabbix.php?action=auditlog.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/reports/audit'
				]
			],
			// #43 Action log view.
			[
				[
					'url' => 'auditacts.php',
					'doc_link' => '/en/manual/web_interface/frontend_sections/reports/action_log'
				]
			],
			// #44 Notifications report view.
			[
				[
					'url' => 'report4.php',
					'doc_link' => '/en/manual/web_interface/frontend_sections/reports/notifications'
				]
			],
			// #45 Host groups list view.
			[
				[
					'url' => 'hostgroups.php',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/hostgroups'
				]
			],
			// #46 Create host group view.
			[
				[
					'url' => 'hostgroups.php?form=create',
					'doc_link' => '/en/manual/config/hosts/host#creating-a-host-group'
				]
			],
			// #47 Update host group view.
			[
				[
					'url' => 'hostgroups.php?form=update&groupid=5',
					'doc_link' => '/en/manual/config/hosts/host#creating-a-host-group'
				]
			],
			// #48 Template list view.
			[
				[
					'url' => 'templates.php',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/templates'
				]
			],
			// #49 Create template view.
			[
				[
					'url' => 'templates.php?form=create',
					'doc_link' => '/en/manual/config/templates/template#creating-a-template'
				]
			],
			// #50 Update template view.
			[
				[
					'url' => 'templates.php?form=update&templateid=10050',
					'doc_link' => '/en/manual/config/templates/template#creating-a-template'
				]
			],
			// #51 Template import popup.
			[
				[
					'url' => 'templates.php',
					'open_button' => 'button:Import',
					'doc_link' => '/en/manual/xml_export_import/templates#importing'
				]
			],
			// #52 Template mass update popup.
			[
				[
					'url' => 'templates.php',
					'open_button' => 'button:Mass update',
					'doc_link' => '/en/manual/config/templates/mass#using-mass-update'
				]
			],
			// #53 Template items list view.
			[
				[
					'url' => 'items.php?context=template',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/templates/items'
				]
			],
			// #54 Template item create form.
			[
				[
					'url' => 'items.php?form=create&hostid=15000&context=template',
					'doc_link' => '/en/manual/config/items/item#configuration'
				]
			],
			// #55 Template item update form.
			[
				[
					'url' => 'items.php?form=update&hostid=15000&itemid=15000&context=template',
					'doc_link' => '/en/manual/config/items/item#configuration'
				]
			],
			// TODO: Uncomment this test case when ZBX-20782 is merged.
//			// #56 Template item Mass update popup.
//			[
//				[
//					'url' => 'items.php?filter_set=1&filter_hostids%5B0%5D=15000&context=template',
//					'open_button' => 'button:Mass update',
//					'doc_link' => '/en/manual/config/items/itemupdate#using-mass-update'
//				]
//			],
			// #57 Template trigger list view.
			[
				[
					'url' => 'triggers.php?context=template',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/templates/triggers'
				]
			],
			// #58 Template trigger create form.
			[
				[
					'url' => 'triggers.php?hostid=15000&form=create&context=template',
					'doc_link' => '/en/manual/config/triggers/trigger#configuration'
				]
			],
			// #59 Template trigger update form.
			[
				[
					'url' => 'triggers.php?form=update&triggerid=99000&context=template',
					'doc_link' => '/en/manual/config/triggers/trigger#configuration'
				]
			],
			// TODO: Uncomment this test case when ZBX-20782 is merged.
//			// #60 Template trigger Mass update popup.
//			[
//				[
//					'url' => 'triggers.php?filter_set=1&filter_hostids%5B0%5D=15000&context=template',
//					'open_button' => 'button:Mass update',
//					'doc_link' => '/en/manual/config/triggers/update#using-mass-update'
//				]
//			],
			// #61 Template graph list view.
			[
				[
					'url' => 'graphs.php?context=template',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/templates/graphs'
				]
			],
			// #62 Template graph create form.
			[
				[
					'url' => 'graphs.php?hostid=15000&form=create&context=template',
					'doc_link' => '/en/manual/config/visualization/graphs/custom#configuring-custom-graphs'
				]
			],
			// #63 Template graph update form.
			[
				[
					'url' => 'graphs.php?form=update&graphid=15000&context=template&filter_hostids%5B0%5D=15000',
					'doc_link' => '/en/manual/config/visualization/graphs/custom#configuring-custom-graphs'
				]
			],
			// #64 Template dashboards list view.
			[
				[
					'url' => 'zabbix.php?action=template.dashboard.list&templateid=10076&context=template',
					'doc_link' => '/en/manual/config/visualization/host_screens'
				]
			],
			// #65 Template dashboard create popup.
			[
				[
					'url' => 'zabbix.php?action=template.dashboard.list&templateid=10076&context=template',
					'open_button' => 'button:Create dashboard',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/dashboard#creating-a-dashboard',
					'cancel_locator' => 'id:dashboard-cancel',
					'alert' => true
				]
			],
			// #66 Template dashboards view mode.
			[
				[
					'url' => 'zabbix.php?action=template.dashboard.edit&dashboardid=50',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/dashboard#creating-a-dashboard'
				]
			],
			// #67 Template dashboard widget edit popup.
			[
				[
					'url' => 'zabbix.php?action=template.dashboard.edit&dashboardid=50',
					'doc_link' => '/en/manual/web_interface/frontend_sections/monitoring/dashboard/widgets',
					'open_button' => 'xpath:(//button[@class="btn-widget-edit"])[1]',
					'cancel_locator' => 'id:dashboard-cancel'
				]
			],
			// TODO: Uncomment this test case and adjust documentation link when ZBX-20773 is merged.
//			// #68 Add Template dashboard page configuration popup.
//			[
//				[
//					'url' => 'zabbix.php?action=template.dashboard.edit&dashboardid=50',
//					'doc_link' => '/en/manual/config/reports#configuration',
//					'open_button' => 'xpath://button[@id="dashboard-add"]',
//					'select_option' => 'xpath://a[text()="Add page"]',
//					'cancel_locator' => 'id:dashboard-cancel'
//				]
//			],
			// #69 Template LLD rule list view.
			[
				[
					'url' => 'host_discovery.php?context=template',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/templates/discovery'
				]
			],
			// #70 Template LLD rule configuration form.
			[
				[
					'url' => 'host_discovery.php?form=create&hostid=15000&context=template',
					'doc_link' => '/en/manual/discovery/low_level_discovery#discovery-rule'
				]
			],
			// #71 Template LLD item prototype list view.
			[
				[
					'url' => 'disc_prototypes.php?parent_discoveryid=15011&context=template',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/templates/discovery/item_prototypes'
				]
			],
			// #72 Template LLD item prototype create form.
			[
				[
					'url' => 'disc_prototypes.php?form=create&parent_discoveryid=15011&context=template',
					'doc_link' => '/en/manual/discovery/low_level_discovery/item_prototypes'
				]
			],
			// #73 Template LLD item prototype edit form.
			[
				[
					'url' => 'disc_prototypes.php?form=update&parent_discoveryid=15011&itemid=15021&context=template',
					'doc_link' => '/en/manual/discovery/low_level_discovery/item_prototypes'
				]
			],
			// TODO: Uncomment this test case when ZBX-20782 is merged.
//			// #74 Template LLD item prototype mass update popup.
//			[
//				[
//					'url' => 'disc_prototypes.php?parent_discoveryid=15011&context=template',
//					'open_button' => 'button:Mass update',
//					'doc_link' => '/en/manual/config/items/itemupdate#using-mass-update'
//				]
//			],
			// #75 Template LLD trigger prototype list view.
			[
				[
					'url' => 'trigger_prototypes.php?parent_discoveryid=15011&context=template',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/templates/discovery/trigger_prototypes'
				]
			],
			// #76 Template LLD trigger prototype create form.
			[
				[
					'url' => 'trigger_prototypes.php?parent_discoveryid=15011&form=create&context=template',
					'doc_link' => '/en/manual/discovery/low_level_discovery/trigger_prototypes'
				]
			],
			// #77 Template LLD trigger prototype edit form.
			[
				[
					'url' => 'trigger_prototypes.php?form=update&parent_discoveryid=15011&triggerid=99008&context=template',
					'doc_link' => '/en/manual/discovery/low_level_discovery/trigger_prototypes'
				]
			],
			// TODO: Uncomment this test case when ZBX-20782 is merged.
//			// #78 Template LLD trigger prototype mass update popup.
//			[
//				[
//					'url' => 'trigger_prototypes.php?parent_discoveryid=15011&context=template',
//					'open_button' => 'button:Mass update',
//					'doc_link' => '/en/manual/config/triggers/update#using-mass-update'
//				]
//			],
			// #79 Template LLD graph prototype list view.
			[
				[
					'url' => 'graphs.php?parent_discoveryid=15011&context=template',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/templates/discovery/graph_prototypes'
				]
			],
			// #80 Template LLD graph prototype create form.
			[
				[
					'url' => 'graphs.php?form=create&parent_discoveryid=15011&context=template',
					'doc_link' => '/en/manual/discovery/low_level_discovery/graph_prototypes'
				]
			],
			// #81 Template LLD graph prototype edit form.
			[
				[
					'url' => 'graphs.php?form=update&parent_discoveryid=15011&graphid=15008&context=template',
					'doc_link' => '/en/manual/discovery/low_level_discovery/graph_prototypes'
				]
			],
			// #82 Template LLD host prototype list view.
			[
				[
					'url' => 'host_prototypes.php?parent_discoveryid=15011&context=template',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/templates/discovery/host_prototypes'
				]
			],
			// #83 Template LLD host prototype create form.
			[
				[
					'url' => 'host_prototypes.php?form=create&parent_discoveryid=15011&context=template',
					'doc_link' => '/en/manual/vm_monitoring#host-prototypes'
				]
			],
			// #84 Template LLD host prototype edit form.
			[
				[
					'url' => 'host_prototypes.php?form=update&parent_discoveryid=15011&hostid=99000&context=template',
					'doc_link' => '/en/manual/vm_monitoring#host-prototypes'
				]
			],
			// #85 Template Web scenario list view.
			[
				[
					'url' => 'httpconf.php?context=template',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/templates/web'
				]
			],
			// #86 Template Web scenario create form.
			[
				[
					'url' => 'httpconf.php?form=create&hostid=15000&context=template',
					'doc_link' => '/en/manual/web_monitoring#configuring-a-web-scenario'
				]
			],
			// #87 Template Web scenario edit form.
			[
				[
					'url' => 'httpconf.php?form=update&hostid=15000&httptestid=15000&context=template',
					'doc_link' => '/en/manual/web_monitoring#configuring-a-web-scenario'
				]
			],
			// TODO: Uncomment this test case and adjust documentation link when ZBX-20773 is merged.
//			// #87 Template Web scenario step configuration form popup.
//			[
//				[
//					'url' => 'httpconf.php?form=update&hostid=15000&httptestid=15000&context=template',
//					'open_button' => 'xpath://a[@id="tab_stepTab"]',
//					'second_open_button' => 'xpath://div[@id="stepTab"]//button[text()="Add"]',
//					'doc_link' => '/en/manual/web_monitoring#configuring-a-web-scenario'
//				]
//			],
			// #88 Host list view.
			[
				[
					'url' => 'zabbix.php?action=host.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/hosts'
				]
			],
			// #89 Create host popup.
			[
				[
					'url' => 'zabbix.php?action=host.list',
					'open_button' => 'button:Create host',
					'doc_link' => '/en/manual/config/hosts/host#configuration'
				]
			],
			// #90 Edit host popup.
			[
				[
					'url' => 'zabbix.php?action=host.list',
					'open_button' => 'xpath://a[text()="Simple form test host"]',
					'doc_link' => '/en/manual/config/hosts/host#configuration'
				]
			],
			// TODO: Uncomment this test case and adjust documentation link when ZBX-20773 is merged.
//			// 91 Create host form view (standalone).
//			[
//				[
//					'url' => 'zabbix.php?action=host.edit',
//					'doc_link' => '/en/manual/config/hosts/host#configuration'
//				]
//			],
			// #92 Host import popup.
			[
				[
					'url' => 'zabbix.php?action=host.list',
					'open_button' => 'button:Import',
					'doc_link' => '/en/manual/xml_export_import/hosts#importing'
				]
			],
			// TODO: Uncomment this test case when ZBX-20782 is merged.
//			// #93 Host mass update popup.
//			[
//				[
//					'url' => 'zabbix.php?action=host.list',
//					'open_button' => 'button:Mass update',
//					'doc_link' => '/en/manual/config/hosts/hostupdate#using-mass-update'
//				]
//			],
			// #94 Host items list view.
			[
				[
					'url' => 'items.php?context=host',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/hosts/items'
				]
			],
			// #95 Host item create form.
			[
				[
					'url' => 'items.php?form=create&hostid=40001&context=host',
					'doc_link' => '/en/manual/config/items/item#configuration'
				]
			],
			// #96 Host item update form.
			[
				[
					'url' => 'items.php?form=update&hostid=40001&itemid=99102&context=host',
					'doc_link' => '/en/manual/config/items/item#configuration'

				]
			],
			// TODO: Uncomment this test case when ZBX-20782 is merged.
//			// #97 Host item Mass update popup.
//			[
//				[
//					'url' => 'items.php?filter_set=1&filter_hostids%5B0%5D=40001&context=host',
//					'open_button' => 'button:Mass update',
//					'doc_link' => '/en/manual/config/items/itemupdate#using-mass-update'
//				]
//			],
			// #98 Host trigger list view.
			[
				[
					'url' => 'triggers.php?context=host',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/hosts/triggers'
				]
			],
			// #99 Host trigger create form.
			[
				[
					'url' => 'triggers.php?hostid=40001&form=create&context=host',
					'doc_link' => '/en/manual/config/triggers/trigger#configuration'
				]
			],
			// #100 Host trigger update form.
			[
				[
					'url' => 'triggers.php?form=update&triggerid=14000&context=host',
					'doc_link' => '/en/manual/config/triggers/trigger#configuration'
				]
			],
			// TODO: Uncomment this test case when ZBX-20782 is merged.
//			// #101 Host trigger Mass update popup.
//			[
//				[
//					'url' => 'triggers.php?filter_set=1&filter_hostids%5B0%5D=40001&context=host',
//					'open_button' => 'button:Mass update',
//					'doc_link' => '/en/manual/config/triggers/update#using-mass-update'
//				]
//			],
			// #102 Host graph list view.
			[
				[
					'url' => 'graphs.php?context=host',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/hosts/graphs'
				]
			],
			// #103 Host graph create form.
			[
				[
					'url' => 'graphs.php?hostid=40001&form=create&context=host',
					'doc_link' => '/en/manual/config/visualization/graphs/custom#configuring-custom-graphs'
				]
			],
			// #104 Host graph update form.
			[
				[
					'url' => 'graphs.php?form=update&graphid=300000&context=host&filter_hostids%5B0%5D=40001',
					'doc_link' => '/en/manual/config/visualization/graphs/custom#configuring-custom-graphs'
				]
			],
			// #105 Host LLD rule list view.
			[
				[
					'url' => 'host_discovery.php?context=host',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/hosts/discovery'
				]
			],
			// #106 Host LLD rule configuration form.
			[
				[
					'url' => 'host_discovery.php?form=create&hostid=40001&context=host',
					'doc_link' => '/en/manual/discovery/low_level_discovery#discovery-rule'
				]
			],
			// #107 Host LLD item prototype list view.
			[
				[
					'url' => 'disc_prototypes.php?parent_discoveryid=133800&context=host',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/hosts/discovery/item_prototypes'
				]
			],
			// #108 Host LLD item prototype create form.
			[
				[
					'url' => 'disc_prototypes.php?form=create&parent_discoveryid=133800&context=host',
					'doc_link' => '/en/manual/discovery/low_level_discovery/item_prototypes'
				]
			],
			// #109 Host LLD item prototype edit form.
			[
				[
					'url' => 'disc_prototypes.php?form=update&parent_discoveryid=133800&itemid=23800&context=host',
					'doc_link' => '/en/manual/discovery/low_level_discovery/item_prototypes'
				]
			],
			// TODO: Uncomment this test case when ZBX-20782 is merged.
//			// #110 Host LLD item prototype mass update popup.
//			[
//				[
//					'url' => 'disc_prototypes.php?cancel=1&parent_discoveryid=133800&context=host',
//					'open_button' => 'button:Mass update',
//					'doc_link' => '/en/manual/config/items/itemupdate#using-mass-update'
//				]
//			],
			// #111 Host LLD trigger prototype list view.
			[
				[
					'url' => 'trigger_prototypes.php?parent_discoveryid=133800&context=host',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/hosts/discovery/trigger_prototypes'
				]
			],
			// #112 Host LLD trigger prototype create form.
			[
				[
					'url' => 'trigger_prototypes.php?parent_discoveryid=133800&form=create&context=host',
					'doc_link' => '/en/manual/discovery/low_level_discovery/trigger_prototypes'
				]
			],
			// #113 Host LLD trigger prototype edit form.
			[
				[
					'url' => 'trigger_prototypes.php?form=update&parent_discoveryid=133800&triggerid=99518&context=host',
					'doc_link' => '/en/manual/discovery/low_level_discovery/trigger_prototypes'
				]
			],
			// TODO: Uncomment this test case when ZBX-20782 is merged.
//			// #114 Host LLD trigger prototype mass update popup.
//			[
//				[
//					'url' => 'trigger_prototypes.php?cancel=1&parent_discoveryid=133800&context=host',
//					'open_button' => 'button:Mass update',
//					'doc_link' => '/en/manual/config/triggers/update#using-mass-update'
//				]
//			],
			// #115 Host LLD graph prototype list view.
			[
				[
					'url' => 'graphs.php?parent_discoveryid=133800&context=host',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/hosts/discovery/graph_prototypes'
				]
			],
			// #116 Host LLD graph prototype create form.
			[
				[
					'url' => 'graphs.php?form=create&parent_discoveryid=133800&context=host',
					'doc_link' => '/en/manual/discovery/low_level_discovery/graph_prototypes'
				]
			],
			// #117 Host LLD graph prototype edit form.
			[
				[
					'url' => 'graphs.php?form=update&parent_discoveryid=133800&graphid=600000&context=host',
					'doc_link' => '/en/manual/discovery/low_level_discovery/graph_prototypes'
				]
			],
			// #118 Host LLD host prototype list view.
			[
				[
					'url' => 'host_prototypes.php?parent_discoveryid=90001&context=host',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/hosts/discovery/host_prototypes'
				]
			],
			// #119 Host LLD host prototype create form.
			[
				[
					'url' => 'host_prototypes.php?form=create&parent_discoveryid=90001&context=host',
					'doc_link' => '/en/manual/vm_monitoring#host-prototypes'
				]
			],
			// #120 Host LLD host prototype edit form.
			[
				[
					'url' => 'host_prototypes.php?form=update&parent_discoveryid=90001&hostid=99200&context=host',
					'doc_link' => '/en/manual/vm_monitoring#host-prototypes'
				]
			],
			// #121 Host Web scenario list view.
			[
				[
					'url' => 'httpconf.php?filter_set=1&filter_hostids%5B0%5D=40001&context=host',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/hosts/web'
				]
			],
			// #122 Host Web scenario create form.
			[
				[
					'url' => 'httpconf.php?form=create&hostid=40001&context=host',
					'doc_link' => '/en/manual/web_monitoring#configuring-a-web-scenario'
				]
			],
			// #123 Host Web scenario edit form.
			[
				[
					'url' => 'httpconf.php?form=update&hostid=40001&httptestid=94&context=host',
					'doc_link' => '/en/manual/web_monitoring#configuring-a-web-scenario'
				]
			],
			// TODO: Uncomment this test case and adjust documentation link when ZBX-20773 is merged.
//			// #124 Host Web scenario step configuration form popup.
//			[
//				[
//					'url' => 'httpconf.php?form=update&hostid=40001&httptestid=94&context=host',
//					'open_button' => 'xpath://a[@id="tab_stepTab"]',
//					'second_open_button' => 'xpath://div[@id="stepTab"]//button[text()="Add"]',
//					'doc_link' => '/en/manual/web_monitoring#configuring-a-web-scenario'
//				]
//			],
			// #125 Maintenance list view.
			[
				[
					'url' => 'maintenance.php',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/maintenance'
				]
			],
			// #126 Create maintenance form view.
			[
				[
					'url' => 'maintenance.php?form=create',
					'doc_link' => '/en/manual/maintenance#configuration'
				]
			],
			// #127 Edit maintenance form view.
			[
				[
					'url' => 'maintenance.php?form=update&maintenanceid=4',
					'doc_link' => '/en/manual/maintenance#configuration'
				]
			],
			// #128 Trigger actions list view.
			[
				[
					'url' => 'actionconf.php?eventsource=0',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/actions'
				]
			],
			// #129 Create trigger action form view.
			[
				[
					'url' => 'actionconf.php?eventsource=0&form=Create+action',
					'doc_link' => '/en/manual/config/notifications/action#configuring-an-action'
				]
			],
			// #130 Edit trigger action form view.
			[
				[
					'url' => 'actionconf.php?eventsource=0&form=update&actionid=3',
					'doc_link' => '/en/manual/config/notifications/action#configuring-an-action'
				]
			],
			// #131 Discovery actions list view.
			[
				[
					'url' => 'actionconf.php?eventsource=1',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/actions'
				]
			],
			// #132 Create discovery action form view.
			[
				[
					'url' => 'actionconf.php?eventsource=1&form=Create+action',
					'doc_link' => '/en/manual/config/notifications/action#configuring-an-action'
				]
			],
			// #133 Edit discovery action form view.
			[
				[
					'url' => 'actionconf.php?eventsource=1&form=update&actionid=2',
					'doc_link' => '/en/manual/config/notifications/action#configuring-an-action'
				]
			],
			// #134 Autoregistration actions list view.
			[
				[
					'url' => 'actionconf.php?eventsource=2',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/actions'
				]
			],
			// #135 Create autoregistration action form view.
			[
				[
					'url' => 'actionconf.php?eventsource=2&form=Create+action',
					'doc_link' => '/en/manual/config/notifications/action#configuring-an-action'
				]
			],
			// #136 Edit autoregistration action form view.
			[
				[
					'url' => 'actionconf.php?eventsource=2&form=update&actionid=9',
					'doc_link' => '/en/manual/config/notifications/action#configuring-an-action'
				]
			],
			// #137 Internal actions list view.
			[
				[
					'url' => 'actionconf.php?eventsource=3',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/actions'
				]
			],
			// #138 Create internal action form view.
			[
				[
					'url' => 'actionconf.php?eventsource=3&form=Create+action',
					'doc_link' => '/en/manual/config/notifications/action#configuring-an-action'
				]
			],
			// #139 Edit internal action form view.
			[
				[
					'url' => 'actionconf.php?eventsource=3&form=update&actionid=4',
					'doc_link' => '/en/manual/config/notifications/action#configuring-an-action'
				]
			],
			// #140 Event correlation list view.
			[
				[
					'url' => 'zabbix.php?action=correlation.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/correlation'
				]
			],
			// #141 Create event correlation form view.
			[
				[
					'url' => 'zabbix.php?action=correlation.edit',
					'doc_link' => '/en/manual/config/event_correlation/global#configuration'
				]
			],
			// #142 Edit event correlation form view.
			[
				[
					'url' => 'zabbix.php?correlationid=99002&action=correlation.edit',
					'doc_link' => '/en/manual/config/event_correlation/global#configuration'
				]
			],
			// #143 Network discovery list view.
			[
				[
					'url' => 'zabbix.php?action=discovery.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/configuration/discovery'
				]
			],
			// #144 Create network discovery form view.
			[
				[
					'url' => 'zabbix.php?action=discovery.edit',
					'doc_link' => '/en/manual/discovery/network_discovery/rule#rule-attributes'
				]
			],
			// #145 Edit network discovery form view.
			[
				[
					'url' => 'zabbix.php?action=discovery.edit&druleid=2',
					'doc_link' => '/en/manual/discovery/network_discovery/rule#rule-attributes'
				]
			],
			// #146 Administration -> General -> GUI view.
			[
				[
					'url' => 'zabbix.php?action=gui.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#gui'
				]
			],
			// #147 Administration -> General -> Autoregistration view.
			[
				[
					'url' => 'zabbix.php?action=autoreg.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#autoregistration'
				]
			],
			// #148 Administration -> General -> Housekeeping view.
			[
				[
					'url' => 'zabbix.php?action=housekeeping.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#housekeeper'
				]
			],
			// #149 Administration -> General -> Audit log view.
			[
				[
					'url' => 'zabbix.php?action=audit.settings.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#audit-log'
				]
			],
			// #150 Administration -> General -> Images -> Icon view.
			[
				[
					'url' => 'zabbix.php?action=image.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#images'
				]
			],
			// #151 Administration -> General -> Images -> Background view.
			[
				[
					'url' => 'zabbix.php?action=image.list&imagetype=2',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#images'
				]
			],
			// #152 Administration -> General -> Images -> Create image view.
			[
				[
					'url' => 'zabbix.php?action=image.edit&imagetype=1',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#images'
				]
			],
			// #153 Administration -> General -> Images -> Edit image view.
			[
				[
					'url' => 'zabbix.php?action=image.edit&imageid=2',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#images'
				]
			],
			// #154 Administration -> General -> Icon mapping list view.
			[
				[
					'url' => 'zabbix.php?action=iconmap.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#icon-mapping'
				]
			],
			// #155 Administration -> General -> Icon mapping -> Create form view.
			[
				[
					'url' => 'zabbix.php?action=iconmap.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#icon-mapping'
				]
			],
			// #156 Administration -> General -> Icon mapping -> Edit form view.
			[
				[
					'url' => 'zabbix.php?action=iconmap.edit&iconmapid=101',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#icon-mapping'
				]
			],
			// #157 Administration -> General -> Regular expressions list view.
			[
				[
					'url' => 'zabbix.php?action=regex.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#regular-expressions'
				]
			],
			// #158 Administration -> General -> Regular expressions -> Ceate form view.
			[
				[
					'url' => 'zabbix.php?action=regex.edit',
					'doc_link' => '/en/manual/regular_expressions#global-regular-expressions'
				]
			],
			// #159 Administration -> General -> Regular expressions -> Edit form view.
			[
				[
					'url' => 'zabbix.php?action=regex.edit&regexid=3',
					'doc_link' => '/en/manual/regular_expressions#global-regular-expressions'
				]
			],
			// #160 Administration -> General -> Macros view.
			[
				[
					'url' => 'zabbix.php?action=macros.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#macros'
				]
			],
			// #161 Administration -> General -> Trigger displaying options view.
			[
				[
					'url' => 'zabbix.php?action=trigdisplay.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#trigger-displaying-options'
				]
			],
			// #162 Administration -> General -> Geographical maps view.
			[
				[
					'url' => 'zabbix.php?action=geomaps.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#geographical-maps'
				]
			],
			// #163 Administration -> General -> Geographical maps view.
			[
				[
					'url' => 'zabbix.php?action=geomaps.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#geographical-maps'
				]
			],
			// #164 Administration -> General -> Modules list view.
			[
				[
					'url' => 'zabbix.php?action=module.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#modules'
				]
			],
			// TODO: Uncomment this test case and adjust documentation link when ZBX-20773 is merged.
//			// #165 Administration -> General -> Module edit view.
//			[
//				[
//					'url' => 'zabbix.php?action=module.list',
//					'open_button' => 'button:Scan directory',
//					'second_open_button' => 'link:1st Module name',
//					'regular_form' => true,
//					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#modules'
//				]
//			],
			// #166 Administration -> General -> Api tokens list view.
			[
				[
					'url' => 'zabbix.php?action=token.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#api-tokens'
				]
			],
			// #167 Administration -> General -> Api tokens -> Create Api token popup.
			[
				[
					'url' => 'zabbix.php?action=token.list',
					'open_button' => 'button:Create API token',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#api-tokens'
				]
			],
			// #168 Administration -> General -> Other view.
			[
				[
					'url' => 'zabbix.php?action=miscconfig.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#other-parameters'
				]
			],
			// #169 Administration -> Proxy list view.
			[
				[
					'url' => 'zabbix.php?action=proxy.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/proxies'
				]
			],
			// #170 Administration -> Create proxy view.
			[
				[
					'url' => 'zabbix.php?action=proxy.edit',
					'doc_link' => '/en/manual/distributed_monitoring/proxies#configuration'
				]
			],
			// #171 Administration -> Proxies -> Edit proxy view.
			[
				[
					'url' => 'zabbix.php?action=proxy.edit&proxyid=20000',
					'doc_link' => '/en/manual/distributed_monitoring/proxies#configuration'
				]
			],
			// #172 Administration -> Authentication view.
			[
				[
					'url' => 'zabbix.php?action=authentication.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/authentication'
				]
			],
			// #173 Administration -> User groups list view.
			[
				[
					'url' => 'zabbix.php?action=usergroup.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/user_groups'
				]
			],
			// #174 Administration -> User groups -> Create user group view.
			[
				[
					'url' => 'zabbix.php?action=usergroup.edit',
					'doc_link' => '/en/manual/config/users_and_usergroups/usergroup#configuration'
				]
			],
			// #175 Administration -> User groups -> Edit user group view.
			[
				[
					'url' => 'zabbix.php?action=usergroup.edit&usrgrpid=7',
					'doc_link' => '/en/manual/config/users_and_usergroups/usergroup#configuration'
				]
			],
			// #176 Administration -> User roles list view.
			[
				[
					'url' => 'zabbix.php?action=userrole.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/user_roles'
				]
			],
			// #177 Administration -> User roles -> Create form view.
			[
				[
					'url' => '/zabbix.php?action=userrole.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/user_roles#default-user-roles'
				]
			],
			// #178 Administration -> User roles -> Edit form view.
			[
				[
					'url' => 'zabbix.php?action=userrole.edit&roleid=3',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/user_roles#default-user-roles'
				]
			],
			// #179 Administration -> Users list view.
			[
				[
					'url' => 'zabbix.php?action=user.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/users'
				]
			],
			// #180 Administration -> Users -> Create form view.
			[
				[
					'url' => 'zabbix.php?action=user.edit',
					'doc_link' => '/en/manual/config/users_and_usergroups/user'
				]
			],
			// #181 Administration -> Users -> Edit form view.
			[
				[
					'url' => 'zabbix.php?action=user.edit&userid=1',
					'doc_link' => '/en/manual/config/users_and_usergroups/user'
				]
			],
			// #182 Administration -> Media type list view.
			[
				[
					'url' => 'zabbix.php?action=mediatype.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/mediatypes'
				]
			],
			// #183 Administration -> Media type -> Create form view.
			[
				[
					'url' => 'zabbix.php?action=mediatype.edit',
					'doc_link' => '/en/manual/config/notifications/media#common-parameters'
				]
			],
			// #184 Administration -> Media type -> Edit form view.
			[
				[
					'url' => 'zabbix.php?action=mediatype.edit&mediatypeid=1',
					'doc_link' => '/en/manual/config/notifications/media#common-parameters'
				]
			],
			// #185 Administration -> Media type -> Import view.
			[
				[
					'url' => 'zabbix.php?action=mediatype.list',
					'open_button' => 'button:Import',
					'doc_link' => '/en/manual/xml_export_import/media#importing'
				]
			],
			// #186 Administration -> Scripts list view.
			[
				[
					'url' => 'zabbix.php?action=script.list',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/scripts'
				]
			],
			// #187 Administration -> Scripts -> Create form view.
			[
				[
					'url' => 'zabbix.php?action=script.edit',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/scripts#configuring-a-global-script'
				]
			],
			// #188 Administration -> Scripts -> Edit form view.
			[
				[
					'url' => 'zabbix.php?action=script.edit&scriptid=2',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/scripts#configuring-a-global-script'
				]
			],
			// #189 Administration -> Queue overview view.
			[
				[
					'url' => 'zabbix.php?action=queue.overview',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/queue#overview-by-item-type'
				]
			],
			// #190 Administration -> Queue overview by proxy view.
			[
				[
					'url' => 'zabbix.php?action=queue.overview.proxy',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/queue#overview-by-proxy'
				]
			],
			// #191 Administration -> Queue details view.
			[
				[
					'url' => 'zabbix.php?action=queue.details',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/queue#list-of-waiting-items'
				]
			],
			// #192 User profile view.
			[
				[
					'url' => 'zabbix.php?action=userprofile.edit',
					'doc_link' => '/en/manual/web_interface/user_profile#user-profile'
				]
			],
			// #193 User setings -> Api tokens list view.
			[
				[
					'url' => 'zabbix.php?action=user.token.list',
					'doc_link' => '/en/manual/web_interface/user_profile#api-tokens'
				]
			],
			// #194 User setings -> Api tokens -> Create Api token popup.
			[
				[
					'url' => 'zabbix.php?action=user.token.list',
					'open_button' => 'button:Create API token',
					'doc_link' => '/en/manual/web_interface/frontend_sections/administration/general#api-tokens'
				]
			]
		];
	}

	/**
	 * @dataProvider getGeneralDocumentationLinkData
	 */
	public function testDocumentationLinks_checkGeneralLinks($data) {
		$this->page->login()->open($data['url']);
		$this->page->waitUntilReady();

		// Perform all necessary actions to get to the form with the documentation link.
		if (array_key_exists('open_button',$data)) {
			// Select all entries before pressing the Mass update button.
			if ($data['open_button'] === 'button:Mass update') {
				$all_entries = $this->query('xpath://input[contains(@id, "all_")]')->asCheckbox()->one()->set(true);
			}

			$this->query($data['open_button'])->one()->click();

			if (array_key_exists('second_open_button', $data)) {
				$this->query($data['second_open_button'])->waitUntilVisible()->one()->click();
			}

			if (array_key_exists('select_option', $data)) {
				$this->query($data['select_option'])->waitUntilVisible()->one()->click();
			}

			// Forms with doc links that are accessible through performing some actions can be located in regularar forms.
			$location = array_key_exists('regular_form', $data) ? $this : COverlayDialogElement::find()->one()->waitUntilReady();
		}
		else {
			$location = $this;
		}

		// Get the documentation link and compare it with expected result.
		$link = $location->query('class:icon-doc-link')->one();
		$this->assertEquals(self::$path_start.self::$version.$data['doc_link'], $link->getAttribute('href'));

		// If the link was located in a popup - close this popup.
		if (array_key_exists('open_button', $data) && !array_key_exists('regular_form', $data)) {
			$location->close();
		}

		// Cancel element creation/update if it impacts execution of next cases.
		if (array_key_exists('cancel_locator', $data)) {
			$this->query($data['cancel_locator'])->waitUntilClickable()->one()->click();
		}

		// Close alert if it prevents cancellation of element creation/update.
		if (CTestArrayHelper::get($data, 'alert')) {
			$this->page->acceptAlert();
		}
	}

	public static function getMapDocumentationLinkData() {
		return [
			// #0 Edit element form.
			[
				[
					'element' => 'xpath://div[@data-id="3"]',
					'doc_link' => '/en/manual/config/visualization/maps/map#adding-elements'
				]
			],
			// #1 Edit shape form.
			[
				[
					'element' => 'xpath://div[@data-id="101"]',
					'doc_link' => '/en/manual/config/visualization/maps/map#adding-shapes'
				]
			],
			// #2 Edit shape form.
			[
				[
					'element' => ['xpath://div[@data-id="7"]', 'xpath://div[@data-id="5"]'],
					'doc_link' => '/en/manual/config/visualization/maps/map#selecting-elements'
				]
			],
			// #1 Edit shape form.
			[
				[
					'element' => ['xpath://div[@data-id="100"]', 'xpath://div[@data-id="101"]'],
					'doc_link' => '/en/manual/config/visualization/maps/map#adding-shapes'
				]
			]
		];
	}
	/**
	 * @dataProvider getMapDocumentationLinkData
	 */
	public function testDocumentationLinks_checkMapElementLinks($data) {
		$this->page->login()->open('sysmap.php?sysmapid=3');
		$this->page->waitUntilReady();

		// Checking element selection documentation links requires pressing control key when selecting elements.
		if (is_array($data['element'])) {
			$keyboard = CElementQuery::getDriver()->getKeyboard();
			$keyboard->pressKey(\Facebook\WebDriver\WebDriverKeys::LEFT_CONTROL);

			foreach ($data['element'] as $element) {
				$this->query($element)->one()->click();
			}

			$keyboard->releaseKey(\Facebook\WebDriver\WebDriverKeys::LEFT_CONTROL);
		}
		else {
			$this->query($data['element'])->one()->click();
		}

		$dialog = $this->query('id:map-window')->one()->waitUntilVisible();
		// Maps contain headers for all map elements, so only the visible one should be checked.
		$link = $dialog->query('class:icon-doc-link')->all()->filter(new CElementFilter(CElementFilter::VISIBLE))->first();

		$this->assertEquals(self::$path_start.self::$version.$data['doc_link'], $link->getAttribute('href'));
	}
}
