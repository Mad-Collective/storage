<?php

namespace spec\Cmp\Storage\Log;

use Cmp\Storage\Date\DateProviderInterface;
use Cmp\Storage\Log\LogWriterInterface;
use PhpSpec\ObjectBehavior;
use Psr\Log\LogLevel;

class DefaultLoggerSpec extends ObjectBehavior
{
    function let(LogWriterInterface $writer, DateProviderInterface $dateProvider)
    {
        $this->beConstructedWith($writer, $dateProvider);
    }

    function it_logs_with_emergency_tag(LogWriterInterface $writer, DateProviderInterface $dateProvider){
        $date = '2012/12/01 12:00:00';
        $dateProvider->getDate('Y-m-d H:i:s')->willReturn($date);
        $message = '['.$date.'] ['.LogLevel::EMERGENCY.'] Dummy Output 1'.PHP_EOL;
        $writer->write($message)->shouldBeCalled();

        $this->emergency("Dummy Output {test}",['test'=>1]);
    }

    function it_logs_with_alert_tag(LogWriterInterface $writer, DateProviderInterface $dateProvider){
        $date = '2012/12/01 12:00:00';
        $dateProvider->getDate('Y-m-d H:i:s')->willReturn($date);
        $message = '['.$date.'] ['.LogLevel::ALERT.'] Dummy Output 1'.PHP_EOL;
        $writer->write($message)->shouldBeCalled();

        $this->alert("Dummy Output {test}",['test'=>1]);
    }

    function it_logs_with_critical_tag(LogWriterInterface $writer, DateProviderInterface $dateProvider){
        $date = '2012/12/01 12:00:00';
        $dateProvider->getDate('Y-m-d H:i:s')->willReturn($date);
        $message = '['.$date.'] ['.LogLevel::CRITICAL.'] Dummy Output 1'.PHP_EOL;
        $writer->write($message)->shouldBeCalled();

        $this->critical("Dummy Output {test}",['test'=>1]);
    }

    function it_logs_with_error_tag(LogWriterInterface $writer, DateProviderInterface $dateProvider){
        $date = '2012/12/01 12:00:00';
        $dateProvider->getDate('Y-m-d H:i:s')->willReturn($date);
        $message = '['.$date.'] ['.LogLevel::ERROR.'] Dummy Output 1'.PHP_EOL;
        $writer->write($message)->shouldBeCalled();

        $this->error("Dummy Output {test}",['test'=>1]);
    }

    function it_logs_with_warning_tag(LogWriterInterface $writer, DateProviderInterface $dateProvider){
        $date = '2012/12/01 12:00:00';
        $dateProvider->getDate('Y-m-d H:i:s')->willReturn($date);
        $message = '['.$date.'] ['.LogLevel::WARNING.'] Dummy Output 1'.PHP_EOL;
        $writer->write($message)->shouldBeCalled();

        $this->warning("Dummy Output {test}",['test'=>1]);
    }

    function it_logs_with_notice_tag(LogWriterInterface $writer, DateProviderInterface $dateProvider){
        $date = '2012/12/01 12:00:00';
        $dateProvider->getDate('Y-m-d H:i:s')->willReturn($date);
        $message = '['.$date.'] ['.LogLevel::NOTICE.'] Dummy Output 1'.PHP_EOL;
        $writer->write($message)->shouldBeCalled();

        $this->notice("Dummy Output {test}",['test'=>1]);
    }

    function it_logs_with_info_tag(LogWriterInterface $writer, DateProviderInterface $dateProvider){
        $date = '2012/12/01 12:00:00';
        $dateProvider->getDate('Y-m-d H:i:s')->willReturn($date);
        $message = '['.$date.'] ['.LogLevel::INFO.'] Dummy Output 1'.PHP_EOL;
        $writer->write($message)->shouldBeCalled();

        $this->info("Dummy Output {test}",['test'=>1]);
    }

    function it_logs_with_debug_tag(LogWriterInterface $writer, DateProviderInterface $dateProvider){
        $date = '2012/12/01 12:00:00';
        $dateProvider->getDate('Y-m-d H:i:s')->willReturn($date);
        $message = '['.$date.'] ['.LogLevel::DEBUG.'] Dummy Output 1'.PHP_EOL;
        $writer->write($message)->shouldBeCalled();

        $this->debug("Dummy Output {test}",['test'=>1]);
    }
}