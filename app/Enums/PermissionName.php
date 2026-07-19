<?php

namespace App\Enums;

enum PermissionName: string
{
    case DashboardView = 'dashboard.view';
    case CompanyManage = 'company.manage';
    case UserManage = 'user.manage';
    case RoleManage = 'role.manage';
    case PermissionManage = 'permission.manage';
    case SiteManage = 'site.manage';
    case DeviceManage = 'device.manage';
    case InventoryManage = 'inventory.manage';
    case MonitoringView = 'monitoring.view';
    case ProblemAcknowledge = 'problem.acknowledge';
    case GraphView = 'graph.view';
    case ReportView = 'report.view';
    case ZabbixConnectionManage = 'zabbix.connection.manage';
}
