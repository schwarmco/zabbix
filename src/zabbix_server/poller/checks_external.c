/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
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

#include "checks_external.h"

#include "zbxexec.h"
#include "zbxsysinfo.h"
#include "zbxtime.h"

extern char	*CONFIG_EXTERNALSCRIPTS;

/******************************************************************************
 *                                                                            *
 * Purpose: retrieve data from script executed on Zabbix server               *
 *                                                                            *
 * Parameters: item           - [IN] item we are interested in                *
 *             result         - [OUT]                                         *
 *                                                                            *
 * Return value: SUCCEED - data successfully retrieved and stored in result   *
 *                         and result_str (as string)                         *
 *               NOTSUPPORTED - requested item is not supported               *
 *                                                                            *
 ******************************************************************************/
int	get_value_external(const zbx_dc_item_t *item, AGENT_RESULT *result)
{
	char		error[ZBX_ITEM_ERROR_LEN_MAX], *cmd = NULL, *buf = NULL;
	size_t		cmd_alloc = ZBX_KIBIBYTE, cmd_offset = 0;
	int		i, ret = NOTSUPPORTED;
	AGENT_REQUEST	request;

	zabbix_log(LOG_LEVEL_DEBUG, "In %s() key:'%s'", __func__, item->key);
	zabbix_log(1, "DBG In %s(), key '%s', timeout = %i", __func__, item->key, item->timeout);

	zbx_init_agent_request(&request);

	if (SUCCEED != zbx_parse_item_key(item->key, &request))
	{
		SET_MSG_RESULT(result, zbx_strdup(NULL, "Invalid item key format."));
		goto out;
	}

	cmd = (char *)zbx_malloc(cmd, cmd_alloc);
	zbx_snprintf_alloc(&cmd, &cmd_alloc, &cmd_offset, "%s/%s", CONFIG_EXTERNALSCRIPTS, get_rkey(&request));

	if (-1 == access(cmd, X_OK))
	{
		SET_MSG_RESULT(result, zbx_dsprintf(NULL, "%s: %s", cmd, zbx_strerror(errno)));
		goto out;
	}

	for (i = 0; i < get_rparams_num(&request); i++)
	{
		const char	*param;
		char		*param_esc;

		param = get_rparam(&request, i);

		param_esc = zbx_dyn_escape_shell_single_quote(param);
		zbx_snprintf_alloc(&cmd, &cmd_alloc, &cmd_offset, " '%s'", param_esc);
		zbx_free(param_esc);
	}

	if (SUCCEED == (ret = zbx_execute(cmd, &buf, error, sizeof(error), item->timeout,
			ZBX_EXIT_CODE_CHECKS_DISABLED, NULL)))
	{
		zbx_rtrim(buf, ZBX_WHITESPACE);

		zbx_set_agent_result_type(result, ITEM_VALUE_TYPE_TEXT, buf);
		zbx_free(buf);
	}
	else
	{
		if (SIG_ERROR != ret)
			ret = NOTSUPPORTED;

		SET_MSG_RESULT(result, zbx_strdup(NULL, error));
	}
out:
	zbx_free(cmd);

	zbx_free_agent_request(&request);

	zabbix_log(LOG_LEVEL_DEBUG, "End of %s():%s", __func__, zbx_result_string(ret));

	return ret;
}
