import DataTable from 'datatables.net-dt';
import { useEffect, useRef, useState } from 'react';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'datatables.net-responsive-dt';
import 'datatables.net-responsive-dt/css/responsive.dataTables.css';

export type ColumnDef = {
    data: string;
    title: string;
    name?: string;
    orderable?: boolean;
    searchable?: boolean;
    className?: string;
    render?: (data: any, type: string, row: any) => string | React.ReactNode;
    width?: string;
};

export type DataTableConfig = {
    ajax?: {
        url: string;
        type?: 'GET' | 'POST';
        data?: (d: any) => any;
        dataSrc?: string | ((json: any) => any);
    };
    columns: ColumnDef[];
    order?: [number, 'asc' | 'desc'][];
    pageLength?: number;
    lengthMenu?: number[][];
    searching?: boolean;
    ordering?: boolean;
    paging?: boolean;
    info?: boolean;
    responsive?: boolean;
    autoWidth?: boolean;
    serverSide?: boolean;
    processing?: boolean;
    dom?: string;
    language?: any;
    drawCallback?: (settings: any) => void;
    initComplete?: (settings: any, json: any) => void;
    buttons?: any[];
};

type Props = {
    config: DataTableConfig;
    data?: any[];
    onRowClick?: (row: any) => void;
    className?: string;
    id?: string;
};

export default function BaseDataTable({
    config,
    data = [],
    onRowClick,
    className = '',
    id = 'datatable',
}: Props) {
    const tableRef = useRef<HTMLTableElement>(null);
    const [table, setTable] = useState<DataTable.Api | null>(null);

    useEffect(() => {
        if (!tableRef.current) return;

        // Destroy existing table if any
        if (table) {
            table.destroy();
        }

        // Initialize DataTable
        const dt = new DataTable(tableRef.current, {
            ...config,
            data: config.serverSide ? undefined : data,
        });

        setTable(dt);

        // Row click handler
        if (onRowClick) {
            dt.on('click', 'tbody tr', function () {
                const rowData = dt.row(this).data();
                onRowClick(rowData);
            });
        }

        // Cleanup
        return () => {
            dt.destroy();
        };
    }, [config, data]);

    // Reload table data
    useEffect(() => {
        if (table && !config.serverSide && data.length > 0) {
            table.clear();
            table.rows.add(data);
            table.draw();
        }
    }, [data, table, config.serverSide]);

    return (
        <div className={`datatable-wrapper ${className}`}>
            <table
                ref={tableRef}
                id={id}
                className="display responsive nowrap"
                style={{ width: '100%' }}
            />
        </div>
    );
}
