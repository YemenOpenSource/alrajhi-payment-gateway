<?php

namespace AlRajhi\PaymentGateway\Support;

class TransactionStatusResolver
{
  public function normalize(mixed $status): ?string
  {
    if ($status === null) {
      return null;
    }

    $normalizedStatus = strtoupper(trim((string) $status));

    return $normalizedStatus !== '' ? $normalizedStatus : null;
  }

  public function isSuccessful(?string $normalizedStatus): bool
  {
    return in_array($normalizedStatus, [
      'SUCCESS',
      'APPROVED',
      'CAPTURED',
      'PAID',
      '1',
    ], true);
  }

  public function isFailure(?string $normalizedStatus): bool
  {
    if ($normalizedStatus === null) {
      return false;
    }

    if (in_array($normalizedStatus, [
      'FAILED',
      'DECLINED',
      'ERROR',
      'NOT CAPTURED',
      'NOT_CAPTURED',
      'NOT APPROVED',
      'NOT_APPROVED',
      'NOT VOIDED',
      'NOT_VOIDED',
      'NOT PROCESSED',
      'NOT_PROCESSED',
      '2',
    ], true)) {
      return true;
    }

    return $this->isDeniedByRisk($normalizedStatus);
  }

  public function isCancelled(?string $normalizedStatus): bool
  {
    return in_array($normalizedStatus, ['CANCELLED', 'CANCELED'], true);
  }

  public function isCaptured(?string $normalizedStatus): bool
  {
    return $normalizedStatus === 'CAPTURED';
  }

  public function isAuthorized(?string $normalizedStatus): bool
  {
    return in_array($normalizedStatus, ['AUTHORIZED', 'AUTHORISED', 'APPROVED'], true);
  }

  public function isPending(?string $normalizedStatus): bool
  {
    return in_array($normalizedStatus, [
      'PENDING',
      'PROCESSING',
      'IN_PROGRESS',
      'HOST TIMEOUT',
      'HOST_TIMEOUT',
    ], true);
  }

  public function isVoided(?string $normalizedStatus): bool
  {
    return $normalizedStatus === 'VOIDED';
  }

  public function isDeniedByRisk(?string $normalizedStatus): bool
  {
    if ($normalizedStatus === null) {
      return false;
    }

    return str_contains($normalizedStatus, 'DENIED BY RISK')
      || str_contains($normalizedStatus, 'DENIED_BY_RISK');
  }

  /**
   * Classify a normalized ARB status into a merchant-facing outcome.
   *
   * @return 'success'|'failure'|'pending'|'voided'|'cancelled'|'unknown'
   */
  public function classify(?string $normalizedStatus): string
  {
    if ($normalizedStatus === null) {
      return 'unknown';
    }

    if ($this->isVoided($normalizedStatus)) {
      return 'voided';
    }

    if ($this->isCancelled($normalizedStatus)) {
      return 'cancelled';
    }

    if ($this->isSuccessful($normalizedStatus)) {
      return 'success';
    }

    if ($this->isFailure($normalizedStatus)) {
      return 'failure';
    }

    if ($this->isPending($normalizedStatus)) {
      return 'pending';
    }

    return 'unknown';
  }
}
