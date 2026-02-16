import { Head } from '@inertiajs/react';
import {
    CheckCircle2,
    XCircle,
    Loader2,
    Link as LinkIcon,
    Unlink,
    AlertTriangle,
    Bug
} from 'lucide-react';
import { useState, useEffect } from 'react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';

type Integration = {
    id: number;
    provider: string;
    name: string;
    status: 'connected' | 'disconnected' | 'error';
    api_url: string;
    api_email: string;
    is_connected: boolean;
    has_valid_token: boolean;
    last_synced_at: string | null;
    error_message: string | null;
};

type DebugLog = {
    timestamp: string;
    type: 'info' | 'error' | 'warning';
    message: string;
    data?: any;
};

export default function IntegrationSettings() {
    const [integrations, setIntegrations] = useState<Integration[]>([]);
    const [isConnecting, setIsConnecting] = useState(false);
    const [showConnectForm, setShowConnectForm] = useState(false);
    const [formData, setFormData] = useState({
        provider: 'hr_system',
        name: '',
        api_url: '',
        email: '',
        password: '',
    });
    const [message, setMessage] = useState<{ type: 'success' | 'error', text: string } | null>(null);

    // ✅ Debug state
    const [debugLogs, setDebugLogs] = useState<DebugLog[]>([]);
    const [showDebug, setShowDebug] = useState(true);

    // ✅ Debug helper
    const addDebugLog = (type: 'info' | 'error' | 'warning', message: string, data?: any) => {
        const log: DebugLog = {
            timestamp: new Date().toLocaleTimeString(),
            type,
            message,
            data,
        };
        setDebugLogs(prev => [log, ...prev].slice(0, 20)); // Keep last 20 logs
        console.log(`[${type.toUpperCase()}] ${message}`, data);
    };

    useEffect(() => {
        addDebugLog('info', 'Component mounted');
        fetchIntegrations();
    }, []);

    const fetchIntegrations = async () => {
        addDebugLog('info', 'Fetching integrations...');

        try {
            const response = await fetch('/api/integrations', {
                headers: {
                    'Accept': 'application/json',
                    'credentials': 'include', // Ensure cookies are sent for session auth
                },
            });
            addDebugLog('info', 'Fetch response received', {
                status: response.status,
                ok: response.ok,
            });

            const data = await response.json();
            addDebugLog('info', 'Response parsed', data);

            if (data.success) {
                setIntegrations(data.data);
                addDebugLog('info', `Loaded ${data.data.length} integrations`);
            } else {
                addDebugLog('error', 'Fetch failed', data);
            }
        } catch (error: any) {
            addDebugLog('error', 'Fetch exception', {
                message: error.message,
                stack: error.stack,
            });
        }
    };

    const handleConnect = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsConnecting(true);
        setMessage(null);

        addDebugLog('info', '=== CONNECT STARTED ===', formData);

        try {
            // ✅ Log request payload
            const payload = {
                provider: formData.provider,
                name: formData.name || null,
                api_url: formData.api_url,
                email: formData.email,
                password: formData.password ? '***HIDDEN***' : null,
            };
            addDebugLog('info', 'Sending request', payload);
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');
            const response = await fetch('/api/integrations/connect', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                credentials:'same-origin',
                body: JSON.stringify(formData),
            });

            addDebugLog('info', 'Response received', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok,
            });

            // ✅ Log response headers
            const headers: Record<string, string> = {};
            response.headers.forEach((value, key) => {
                headers[key] = value;
            });
            addDebugLog('info', 'Response headers', headers);

            // ✅ Get raw response text first
            const responseText = await response.text();
            addDebugLog('info', 'Raw response text', responseText);

            let data;
            try {
                data = JSON.parse(responseText);
                addDebugLog('info', 'Parsed response data', data);
            } catch (parseError: any) {
                addDebugLog('error', 'JSON parse failed', {
                    error: parseError.message,
                    rawText: responseText,
                });
                throw new Error('Invalid JSON response from server');
            }

            // ✅ Check response structure
            addDebugLog('info', 'Response structure check', {
                hasSuccess: 'success' in data,
                successValue: data.success,
                hasMessage: 'message' in data,
                hasData: 'data' in data,
                dataType: typeof data.data,
            });

            if (data.success) {
                addDebugLog('info', '✅ Connection successful', data.data);

                setMessage({ type: 'success', text: data.message });
                setShowConnectForm(false);
                fetchIntegrations();
                setFormData({
                    provider: 'hr_system',
                    name: '',
                    api_url: '',
                    email: '',
                    password: '',
                });
            } else {
                addDebugLog('error', '❌ Connection failed', {
                    message: data.message,
                    data: data.data,
                });

                setMessage({ type: 'error', text: data.message });
            }
        } catch (error: any) {
            addDebugLog('error', '❌ Exception caught', {
                message: error.message,
                stack: error.stack,
                name: error.name,
            });

            setMessage({
                type: 'error',
                text: `Connection failed: ${error.message}`
            });
        } finally {
            setIsConnecting(false);
            addDebugLog('info', '=== CONNECT FINISHED ===');
        }
    };

    const handleDisconnect = async (provider: string) => {
        if (!confirm('Are you sure you want to disconnect this integration?')) {
            return;
        }

        addDebugLog('info', 'Disconnecting', { provider });

        try {
            const response = await fetch(`/api/integrations/${provider}`, {
                method: 'DELETE',
            });

            const data = await response.json();
            addDebugLog('info', 'Disconnect response', data);

            if (data.success) {
                setMessage({ type: 'success', text: data.message });
                fetchIntegrations();
            } else {
                setMessage({ type: 'error', text: data.message });
            }
        } catch (error: any) {
            addDebugLog('error', 'Disconnect failed', error);
            setMessage({ type: 'error', text: 'Disconnect failed.' });
        }
    };

    const handleTest = async (provider: string) => {
        addDebugLog('info', 'Testing connection', { provider });

        try {
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');
            const response = await fetch(`/api/integrations/${provider}/test`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                credentials:'same-origin',
            });

            const data = await response.json();
            addDebugLog('info', 'Test response', data);

            if (data.success) {
                setMessage({ type: 'success', text: 'Connection test successful!' });
                fetchIntegrations();
            } else {
                setMessage({ type: 'error', text: data.message });
            }
        } catch (error: any) {
            addDebugLog('error', 'Test failed', error);
            setMessage({ type: 'error', text: 'Test failed.' });
        }
    };

    return (
        <AppLayout>
            <Head title="Integration Settings" />

            <div className="p-6 space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Integration Settings</h1>
                        <p className="text-muted-foreground mt-2">
                            Connect to external systems to sync data automatically
                        </p>
                    </div>

                    {/* ✅ Debug toggle */}
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setShowDebug(!showDebug)}
                    >
                        <Bug className="h-4 w-4 mr-2" />
                        {showDebug ? 'Hide' : 'Show'} Debug
                    </Button>
                </div>

                {/* ✅ Debug Console */}
                {showDebug && (
                    <Card className="bg-gray-900 text-gray-100">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-white flex items-center gap-2">
                                    <Bug className="h-5 w-5" />
                                    Debug Console
                                </CardTitle>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => setDebugLogs([])}
                                    className="text-white border-white"
                                >
                                    Clear Logs
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent className="max-h-96 overflow-y-auto font-mono text-xs space-y-2">
                            {debugLogs.length === 0 ? (
                                <div className="text-gray-400">No logs yet...</div>
                            ) : (
                                debugLogs.map((log, index) => (
                                    <div
                                        key={index}
                                        className={`p-2 rounded ${log.type === 'error'
                                            ? 'bg-red-900/30 border border-red-700'
                                            : log.type === 'warning'
                                                ? 'bg-yellow-900/30 border border-yellow-700'
                                                : 'bg-blue-900/30 border border-blue-700'
                                            }`}
                                    >
                                        <div className="flex items-start gap-2">
                                            <span className="text-gray-400">[{log.timestamp}]</span>
                                            <span
                                                className={`font-bold ${log.type === 'error'
                                                    ? 'text-red-400'
                                                    : log.type === 'warning'
                                                        ? 'text-yellow-400'
                                                        : 'text-blue-400'
                                                    }`}
                                            >
                                                {log.type.toUpperCase()}:
                                            </span>
                                            <span className="flex-1">{log.message}</span>
                                        </div>
                                        {log.data && (
                                            <pre className="mt-2 text-gray-300 overflow-x-auto">
                                                {JSON.stringify(log.data, null, 2)}
                                            </pre>
                                        )}
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>
                )}

                {/* Messages */}
                {message && (
                    <Alert className={message.type === 'success' ? 'border-green-500' : 'border-red-500'}>
                        {message.type === 'success' ? (
                            <CheckCircle2 className="h-4 w-4 text-green-600" />
                        ) : (
                            <XCircle className="h-4 w-4 text-red-600" />
                        )}
                        <AlertDescription>{message.text}</AlertDescription>
                    </Alert>
                )}

                {/* Existing Integrations */}
                <div className="grid gap-4">
                    {integrations.length === 0 ? (
                        <Card>
                            <CardContent className="pt-6 text-center text-muted-foreground">
                                No integrations configured yet
                            </CardContent>
                        </Card>
                    ) : (
                        integrations.map((integration) => (
                            <Card key={integration.id}>
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-3">
                                            <CardTitle>{integration.name}</CardTitle>
                                            <Badge variant={
                                                integration.status === 'connected' ? 'default' :
                                                    integration.status === 'error' ? 'destructive' :
                                                        'secondary'
                                            }>
                                                {integration.status === 'connected' && <CheckCircle2 className="h-3 w-3 mr-1" />}
                                                {integration.status === 'error' && <AlertTriangle className="h-3 w-3 mr-1" />}
                                                {integration.status}
                                            </Badge>
                                        </div>
                                        <div className="flex gap-2">
                                            {integration.is_connected && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => handleTest(integration.provider)}
                                                >
                                                    Test Connection
                                                </Button>
                                            )}
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                onClick={() => handleDisconnect(integration.provider)}
                                            >
                                                <Unlink className="h-4 w-4 mr-2" />
                                                Disconnect
                                            </Button>
                                        </div>
                                    </div>
                                    <CardDescription>
                                        {integration.api_url}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Email:</span>
                                            <span>{integration.api_email}</span>
                                        </div>
                                        {integration.last_synced_at && (
                                            <div className="flex justify-between">
                                                <span className="text-muted-foreground">Last Sync:</span>
                                                <span>{new Date(integration.last_synced_at).toLocaleString()}</span>
                                            </div>
                                        )}
                                        {integration.error_message && (
                                            <Alert variant="destructive" className="mt-2">
                                                <AlertDescription>{integration.error_message}</AlertDescription>
                                            </Alert>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))
                    )}
                </div>

                {/* Add New Integration */}
                {!showConnectForm ? (
                    <Button onClick={() => setShowConnectForm(true)}>
                        <LinkIcon className="h-4 w-4 mr-2" />
                        Add New Integration
                    </Button>
                ) : (
                    <Card>
                        <CardHeader>
                            <CardTitle>Connect to External System</CardTitle>
                            <CardDescription>
                                Enter your API credentials to establish connection
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleConnect} className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="provider">Provider</Label>
                                        <select
                                            id="provider"
                                            className="w-full border rounded-md px-3 py-2"
                                            value={formData.provider}
                                            onChange={(e) => setFormData({ ...formData, provider: e.target.value })}
                                        >
                                            <option value="hr_system">HR System</option>
                                            <option value="payroll_system">Payroll System</option>
                                        </select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="name">Integration Name (Optional)</Label>
                                        <Input
                                            id="name"
                                            placeholder="e.g., Main HR System"
                                            value={formData.name}
                                            onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="api_url">API URL *</Label>
                                    <Input
                                        id="api_url"
                                        type="url"
                                        placeholder="https://api.sadata.id/api"
                                        required
                                        value={formData.api_url}
                                        onChange={(e) => setFormData({ ...formData, api_url: e.target.value })}
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        Enter your API base URL. Do not include /auth/login
                                    </p>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="email">Email *</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            placeholder="your@email.com"
                                            required
                                            value={formData.email}
                                            onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="password">Password *</Label>
                                        <Input
                                            id="password"
                                            type="password"
                                            placeholder="••••••••"
                                            required
                                            value={formData.password}
                                            onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                                        />
                                    </div>
                                </div>

                                <div className="flex gap-3 justify-end">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setShowConnectForm(false)}
                                        disabled={isConnecting}
                                    >
                                        Cancel
                                    </Button>
                                    <Button type="submit" disabled={isConnecting}>
                                        {isConnecting ? (
                                            <>
                                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                Connecting...
                                            </>
                                        ) : (
                                            <>
                                                <LinkIcon className="mr-2 h-4 w-4" />
                                                Connect
                                            </>
                                        )}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
