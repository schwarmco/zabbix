/*
** Zabbix
** Copyright (C) 2001-2024 Zabbix SIA
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

#ifndef ZABBIX_CACHEHISTORY_H
#define ZABBIX_CACHEHISTORY_H

#include "zbxtrends.h"
#include "zbxhistory.h"
#include "zbxcacheconfig.h"
#include "zbxshmem.h"

#define ZBX_HC_PROXYQUEUE_STATE_NORMAL 0
#define ZBX_HC_PROXYQUEUE_STATE_WAIT 1

/* the maximum time spent synchronizing history */
#define ZBX_HC_SYNC_TIME_MAX	10

/* the maximum number of items in one synchronization batch */
#define ZBX_HC_SYNC_MAX		1000
#define ZBX_HC_TIMER_MAX	(ZBX_HC_SYNC_MAX / 2)
#define ZBX_HC_TIMER_SOFT_MAX	(ZBX_HC_TIMER_MAX - 10)

#define ZBX_SYNC_DONE		0
#define	ZBX_SYNC_MORE		1

typedef struct
{
	zbx_uint64_t	history_counter;	/* the total number of processed values */
	zbx_uint64_t	history_float_counter;	/* the number of processed float values */
	zbx_uint64_t	history_uint_counter;	/* the number of processed uint values */
	zbx_uint64_t	history_str_counter;	/* the number of processed str values */
	zbx_uint64_t	history_log_counter;	/* the number of processed log values */
	zbx_uint64_t	history_text_counter;	/* the number of processed text values */
	zbx_uint64_t	history_bin_counter;	/* the number of processed bin values */
	zbx_uint64_t	notsupported_counter;	/* the number of processed not supported items */
}
zbx_dc_stats_t;

/* the write cache statistics */
typedef struct
{
	zbx_dc_stats_t	stats;
	zbx_uint64_t	history_free;
	zbx_uint64_t	history_total;
	zbx_uint64_t	index_free;
	zbx_uint64_t	index_total;
	zbx_uint64_t	trend_free;
	zbx_uint64_t	trend_total;
}
zbx_wcache_info_t;

void	zbx_sync_history_cache(const zbx_events_funcs_t *events_cbs, int *values_num, int *triggers_num, int *more);
void	zbx_log_sync_history_cache_progress(void);

#define ZBX_SYNC_NONE	0
#define ZBX_SYNC_ALL	1

typedef void (*zbx_history_sync_f)(int *values_num, int *triggers_num, const zbx_events_funcs_t *events_cbs, int *more);

int	zbx_init_database_cache(zbx_get_program_type_f get_program_type, zbx_history_sync_f sync_history,
		zbx_uint64_t history_cache_size, zbx_uint64_t history_index_cache_size,zbx_uint64_t *trends_cache_size,
		char **error);

void	zbx_free_database_cache(int sync, const zbx_events_funcs_t *events_cbs);

#define ZBX_STATS_HISTORY_COUNTER	0
#define ZBX_STATS_HISTORY_FLOAT_COUNTER	1
#define ZBX_STATS_HISTORY_UINT_COUNTER	2
#define ZBX_STATS_HISTORY_STR_COUNTER	3
#define ZBX_STATS_HISTORY_LOG_COUNTER	4
#define ZBX_STATS_HISTORY_TEXT_COUNTER	5
#define ZBX_STATS_NOTSUPPORTED_COUNTER	6
#define ZBX_STATS_HISTORY_TOTAL		7
#define ZBX_STATS_HISTORY_USED		8
#define ZBX_STATS_HISTORY_FREE		9
#define ZBX_STATS_HISTORY_PUSED		10
#define ZBX_STATS_HISTORY_PFREE		11
#define ZBX_STATS_TREND_TOTAL		12
#define ZBX_STATS_TREND_USED		13
#define ZBX_STATS_TREND_FREE		14
#define ZBX_STATS_TREND_PUSED		15
#define ZBX_STATS_TREND_PFREE		16
#define ZBX_STATS_HISTORY_INDEX_TOTAL	17
#define ZBX_STATS_HISTORY_INDEX_USED	18
#define ZBX_STATS_HISTORY_INDEX_FREE	19
#define ZBX_STATS_HISTORY_INDEX_PUSED	20
#define ZBX_STATS_HISTORY_INDEX_PFREE	21
#define ZBX_STATS_HISTORY_BIN_COUNTER	22

/* 'zbx_pp_value_opt_t' element 'flags' values */
#define ZBX_PP_VALUE_OPT_NONE		0x0000	/* 'zbx_pp_value_opt_t' has no data */
#define ZBX_PP_VALUE_OPT_META		0x0001	/* 'zbx_pp_value_opt_t' has log metadata ('mtime' and 'lastlogsize') */
#define ZBX_PP_VALUE_OPT_LOG		0x0002	/* 'zbx_pp_value_opt_t' has 'timestamp', 'severity', 'logeventid' and */
						/* 'source' data */

/* This structure is complementary data if value comes from preprocessing. */
typedef struct
{
	zbx_uint32_t	flags;
	int		mtime;
	int		timestamp;
	int		severity;
	int		logeventid;
	zbx_uint64_t	lastlogsize;
	char		*source;
}
zbx_pp_value_opt_t;

void	zbx_pp_value_opt_clear(zbx_pp_value_opt_t *opt);

void	*zbx_dc_get_stats(int request);
void	zbx_dc_get_stats_all(zbx_wcache_info_t *wcache_info);

zbx_uint64_t	zbx_dc_get_nextid(const char *table_name, int num);

void	zbx_dc_update_interfaces_availability(void);

void	zbx_hc_get_diag_stats(zbx_uint64_t *items_num, zbx_uint64_t *values_num);
void	zbx_hc_get_mem_stats(zbx_shmem_stats_t *data, zbx_shmem_stats_t *index);
void	zbx_hc_get_items(zbx_vector_uint64_pair_t *items);

int	zbx_db_trigger_queue_locked(void);
void	zbx_db_trigger_queue_unlock(void);

void	zbx_dc_add_history(zbx_uint64_t itemid, unsigned char item_value_type, unsigned char item_flags,
		AGENT_RESULT *result, const zbx_timespec_t *ts, unsigned char state, const char *error);
void	zbx_dc_add_history_variant(zbx_uint64_t itemid, unsigned char value_type, unsigned char item_flags,
		zbx_variant_t *value, zbx_timespec_t ts, const zbx_pp_value_opt_t *value_opt);
void	zbx_dc_flush_history(void);

void	zbx_dbcache_lock(void);
void	zbx_dbcache_unlock(void);

void	zbx_hc_pop_items(zbx_vector_ptr_t *history_items);
void	zbx_hc_push_items(zbx_vector_ptr_t *history_items);
void	zbx_hc_get_item_values(zbx_dc_history_t *history, zbx_vector_ptr_t *history_items);
int	hc_queue_get_size(void);
void	zbx_hc_free_item_values(zbx_dc_history_t *history, int history_num);
void	zbx_dc_history_clean_value(zbx_dc_history_t *history);
void	zbx_dbcache_set_history_num(int num);
int	zbx_dbcache_get_history_num(void);

void	zbx_hc_proxyqueue_clear(void);
int	zbx_hc_proxyqueue_dequeue(zbx_uint64_t proxyid);
void	zbx_hc_proxyqueue_enqueue(zbx_uint64_t proxyid);

void	DCmodule_sync_history(int history_float_num, int history_integer_num, int history_string_num,
		int history_text_num, int history_log_num, ZBX_HISTORY_FLOAT *history_float,
		ZBX_HISTORY_INTEGER *history_integer, ZBX_HISTORY_STRING *history_string,
		ZBX_HISTORY_TEXT *history_text, ZBX_HISTORY_LOG *history_log);

void	DCmodule_prepare_history(zbx_dc_history_t *history, int history_num, ZBX_HISTORY_FLOAT *history_float,
		int *history_float_num, ZBX_HISTORY_INTEGER *history_integer, int *history_integer_num,
		ZBX_HISTORY_STRING *history_string, int *history_string_num, ZBX_HISTORY_TEXT *history_text,
		int *history_text_num, ZBX_HISTORY_LOG *history_log, int *history_log_num);

void	DCmass_prepare_history(zbx_dc_history_t *history, zbx_history_sync_item_t *items, const int *errcodes,
		int history_num, zbx_add_event_func_t add_event_cb, zbx_vector_ptr_t *item_diff,
		zbx_vector_ptr_t *inventory_values, int compression_age, zbx_vector_uint64_pair_t *proxy_subscriptions);

int	DBmass_add_history(zbx_dc_history_t *history, int history_num);

void	DBmass_update_items(const zbx_vector_ptr_t *item_diff, const zbx_vector_ptr_t *inventory_values);

void	DCinventory_value_free(zbx_inventory_value_t *inventory_value);

void	recalculate_triggers(const zbx_dc_history_t *history, int history_num,
		const zbx_vector_uint64_t *history_itemids, const zbx_history_sync_item_t *history_items,
		const int *history_errcodes, const zbx_vector_ptr_t *timers, zbx_add_event_func_t add_event_cb,
		zbx_vector_ptr_t *trigger_diff, zbx_uint64_t *itemids, zbx_timespec_t *timespecs,
		zbx_hashset_t *trigger_info, zbx_vector_dc_trigger_t *trigger_order);

void	DCmass_update_trends(const zbx_dc_history_t *history, int history_num, ZBX_DC_TREND **trends,
		int *trends_num, int compression_age);

void	DCupdate_trends(zbx_vector_uint64_pair_t *trends_diff);

void	DBmass_update_trends(const ZBX_DC_TREND *trends, int trends_num,
		zbx_vector_uint64_pair_t *trends_diff);

int	hc_get_history_compression_age(void);

void	DCexport_history_and_trends(const zbx_dc_history_t *history, int history_num,
		const zbx_vector_uint64_t *itemids, zbx_history_sync_item_t *items, const int *errcodes,
		const ZBX_DC_TREND *trends, int trends_num, int history_export_enabled,
		zbx_vector_connector_filter_t *connector_filters, unsigned char **data, size_t *data_alloc,
		size_t *data_offset);

zbx_shmem_info_t	*zbx_dbcache_get_hc_mem(void);

void	zbx_dbcache_setproxyqueue_state(int proxyqueue_state);
int	zbx_dbcache_getproxyqueue_state(void);

zbx_uint64_t	zbx_hc_proxyqueue_peek(void);
#endif
