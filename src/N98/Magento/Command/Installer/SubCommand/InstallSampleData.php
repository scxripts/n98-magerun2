<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\AbstractSubCommand;
use N98\Util\OperatingSystem;
use Symfony\Component\Process\ProcessBuilder;

class InstallSampleData extends AbstractSubCommand
{
    /**
     * @return bool
     */
    public function execute()
    {
        if ($this->input->getOption('noDownload')) {
            return false;
        }

        $installationFolder = $this->config->getString('installationFolder');
        chdir($installationFolder);

        $dialog = $this->getCommand()->getHelper('dialog');

        if ($this->input->getOption('installSampleData') !== null) {
            $installSampleData = $this->getCommand()->parseBoolOption($this->input->getOption('installSampleData'));
        } else {
            $installSampleData = $dialog->askConfirmation(
                $this->output,
                '<question>Install sample data?</question> <comment>[y]</comment>: '
            );
        }

        if ($installSampleData) {
            $this->runSampleDataInstaller();
        }
    }

    protected function runSampleDataInstaller()
    {
        $this->runMagentoCommand('sampledata:deploy');
        $this->runMagentoCommand('setup:upgrade');
    }

    /**
     * @return void
     */
    private function runMagentoCommand($command)
    {
        $processBuilder = new ProcessBuilder([
            'php',
            'bin/magento',
            $command,
        ]);

        if (!OperatingSystem::isWindows()) {
            $processBuilder->setPrefix('/usr/bin/env');
        }

        $process = $processBuilder->getProcess();
        $process->setTimeout(86400);
        $process->start();
        $process->wait(function ($type, $buffer) {
            $this->output->write('bin/magento > ' . $buffer, false);
        });
    }
}
