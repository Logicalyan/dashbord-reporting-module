import { format } from 'date-fns';
import { Calendar as CalendarIcon, RefreshCw, CheckCircle2, XCircle } from 'lucide-react';
import { useState } from 'react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
// import { cn } from '@/lib/utils';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

type SyncStatus = {
    has_token: boolean;
    last_sync_at: string | null;
    last_sync_stats: {
        total: number;
        created: number;
        updated: number;
        skipped: number;
        errors: number;
    } | null;
    is_syncing: boolean;
};

export function AttendanceSyncPanel() {
    const [dateRange, setDateRange] = useState<{ from: Date; to: Date }>({
        from: new Date(),
        to: new Date(),
    });
    const [isSyncing, setIsSyncing] = useState(false);
    const [syncResult, setSyncResult] = useState<any>(null);
    const [status, setStatus] = useState<SyncStatus | null>(null);

    const fetchStatus = async () => {
        const response = await fetch('/api/external/sync/status');
        const data = await response.json();
        setStatus(data.data);
    };

    const handleSync = async () => {
        setIsSyncing(true);
        setSyncResult(null);

        try {
            const response = await fetch('/api/external/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    start_date: format(dateRange.from, 'yyyy-MM-dd'),
                    end_date: format(dateRange.to, 'yyyy-MM-dd'),
                }),
            });

            const data = await response.json();

            if (data.success) {
                setSyncResult({ success: true, data: data.data });
                fetchStatus();
            } else {
                setSyncResult({ success: false, message: data.message });
            }
        } catch (error: any) {
            setSyncResult({ success: false, message: `${error.message || 'Network error'}` });
        } finally {
            setIsSyncing(false);
        }
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <RefreshCw className="h-5 w-5" />
                    Sync Attendance from HR System
                </CardTitle>
                <CardDescription>
                    Sync attendance data from external HR system to local database
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                {/* Date Range Picker */}
                <div className="flex gap-4 items-center">
                    <Popover>
                        <PopoverTrigger asChild>
                            <Button variant="outline" className="justify-start text-left font-normal">
                                <CalendarIcon className="mr-2 h-4 w-4" />
                                {dateRange.from ? (
                                    format(dateRange.from, 'PPP')
                                ) : (
                                    <span>Pick start date</span>
                                )}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent className="w-auto p-0">
                            <Calendar
                                mode="single"
                                selected={dateRange.from}
                                onSelect={(date) => date && setDateRange({ ...dateRange, from: date })}
                            />
                        </PopoverContent>
                    </Popover>

                    <span>to</span>

                    <Popover>
                        <PopoverTrigger asChild>
                            <Button variant="outline" className="justify-start text-left font-normal">
                                <CalendarIcon className="mr-2 h-4 w-4" />
                                {dateRange.to ? (
                                    format(dateRange.to, 'PPP')
                                ) : (
                                    <span>Pick end date</span>
                                )}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent className="w-auto p-0">
                            <Calendar
                                mode="single"
                                selected={dateRange.to}
                                onSelect={(date) => date && setDateRange({ ...dateRange, to: date })}
                            />
                        </PopoverContent>
                    </Popover>
                </div>

                {/* Sync Button */}
                <Button
                    onClick={handleSync}
                    disabled={isSyncing || !status?.has_token}
                    className="w-full"
                >
                    {isSyncing ? (
                        <>
                            <RefreshCw className="mr-2 h-4 w-4 animate-spin" />
                            Syncing...
                        </>
                    ) : (
                        <>
                            <RefreshCw className="mr-2 h-4 w-4" />
                            Sync Now
                        </>
                    )}
                </Button>

                {/* Sync Result */}
                {syncResult && (
                    <Alert className={syncResult.success ? 'border-green-500' : 'border-red-500'}>
                        {syncResult.success ? (
                            <CheckCircle2 className="h-4 w-4 text-green-600" />
                        ) : (
                            <XCircle className="h-4 w-4 text-red-600" />
                        )}
                        <AlertDescription>
                            {syncResult.success ? (
                                <div className="space-y-1">
                                    <p className="font-medium">Sync completed successfully!</p>
                                    <ul className="text-sm space-y-1">
                                        <li>Total: {syncResult.data.total}</li>
                                        <li>Created: {syncResult.data.created}</li>
                                        <li>Updated: {syncResult.data.updated}</li>
                                        <li>Skipped: {syncResult.data.skipped}</li>
                                        {syncResult.data.errors > 0 && (
                                            <li className="text-orange-600">
                                                Errors: {syncResult.data.errors}
                                            </li>
                                        )}
                                    </ul>
                                </div>
                            ) : (
                                <p>{syncResult.message}</p>
                            )}
                        </AlertDescription>
                    </Alert>
                )}

                {/* Last Sync Info */}
                {status?.last_sync_at && (
                    <div className="text-sm text-muted-foreground">
                        Last sync: {new Date(status.last_sync_at).toLocaleString()}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
