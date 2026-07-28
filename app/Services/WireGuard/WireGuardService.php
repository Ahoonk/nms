<?php

namespace App\Services\WireGuard;

class WireGuardService
{
    public function peers(): array
    {
        $output = shell_exec('sudo /usr/bin/wg show all dump');

        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));

        $peers = [];

        foreach ($lines as $line) {

            $cols = explode("\t", $line);

            // baris pertama adalah interface
            if (count($cols) < 8) {
                continue;
            }

            $handshake = (int) $cols[5];

            $peers[] = [
                'interface'      => $cols[0],
                'public_key'     => $cols[1],
                'endpoint'       => $cols[3],
                'allowed_ips'    => $cols[4],
                'last_handshake' => $handshake,
                'rx_bytes'       => (int) $cols[6],
                'tx_bytes'       => (int) $cols[7],
                'online'         => $handshake > (time() - 180),
            ];
        }

        return $peers;
    }
}
