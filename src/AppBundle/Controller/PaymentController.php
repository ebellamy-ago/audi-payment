<?php

namespace AppBundle\Controller;

use AppBundle\Entity\TransactionHistory;
use AppBundle\Service\OrderProducer;
use AppBundle\Service\StorageService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mullenlowe\CommonBundle\Controller\MullenloweRestController;
use Mullenlowe\CommonBundle\Exception\BadRequestHttpException;
use Mullenlowe\CommonBundle\Exception\NotFoundHttpException;
use Mullenlowe\CommonBundle\Security\Guard\JWTTokenAuthenticator;
use Mullenlowe\CommonBundle\Security\User\AuthUserProvider;
use Mullenlowe\PayPluginBundle\Exceptions\UndefinedProviderException;
use Mullenlowe\PayPluginBundle\Model\AbstractTransaction;
use Mullenlowe\PayPluginBundle\Model\MagellanStatusTransaction;
use Mullenlowe\PayPluginBundle\Model\StatusTransactionInterface;
use Mullenlowe\PayPluginBundle\Service\Provider\Providers;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\UriSigner;
use Mullenlowe\PayPluginBundle\Service\Provider\Magellan\MagellanProvider;

/**
 * Class PaymentController
 * @package AppBundle\Controller
 */
class PaymentController extends MullenloweRestController
{
    const CONTEXT = 'Payment';

    const FINALIZE = 'Finalize';
    const CANCEL = 'Cancel';

    /**
     * @var UriSigner
     */
    private $uriSigner;

    /**
     * PaymentController constructor.
     * @param JWTTokenAuthenticator $authenticator
     * @param UriSigner             $uriSigner
     */
    public function __construct(JWTTokenAuthenticator $authenticator, UriSigner $uriSigner, AuthUserProvider $authUserProvider)
    {
        parent::__construct($authenticator, $authUserProvider);

        $this->uriSigner = $uriSigner;
    }

    /**
     * @Rest\Post("/", name="_payment")
     * @ParamConverter(name="transaction", converter="transaction_converter")
     * @SWG\Post(
     *     path="/",
     *     description="Retrieve payments informations like a payment page.",
     *     operationId="retrievePaymentsInformations",
     *     tags={"Payment"},
     *     @SWG\Parameter(
     *         name="provider",
     *         required=true,
     *         description="Name of the payment provider (ex: magellan).",
     *         in="body",
     *         @SWG\Schema(
     *             @SWG\Property(property="provider", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="currency",
     *         required=true,
     *         in="body",
     *         description="Currency in which the transaction is established",
     *         @SWG\Schema(
     *             @SWG\Property(property="currency", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="reference_id",
     *         required=true,
     *         in="body",
     *         description="Own reference transaction to the merchant",
     *         @SWG\Schema(
     *             @SWG\Property(property="reference_id", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="amount",
     *         required=true,
     *         in="body",
     *         description="Transaction amount",
     *         @SWG\Schema(
     *             @SWG\Property(property="amount", type="integer")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="origin",
     *         required=true,
     *         in="body",
     *         description="Front origin",
     *         @SWG\Schema(
     *             @SWG\Property(property="origin", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="lastname",
     *         required=true,
     *         in="body",
     *         description="The last name of the buyer",
     *         @SWG\Schema(
     *             @SWG\Property(property="lastname", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="phone",
     *         required=true,
     *         in="body",
     *         description="The buyer phone",
     *         @SWG\Schema(
     *             @SWG\Property(property="phone", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         required=true,
     *         in="body",
     *         description="The name of the buyer",
     *         @SWG\Schema(
     *             @SWG\Property(property="name", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_login",
     *         required=true,
     *         in="body",
     *         description="Merchant login",
     *         @SWG\Schema(
     *             @SWG\Property(property="merchant_login", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_pwd",
     *         required=true,
     *         in="body",
     *         description="Merchant password",
     *         @SWG\Schema(
     *             @SWG\Property(property="merchant_pwd", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_id",
     *         required=true,
     *         in="body",
     *         description="Merchant id",
     *         @SWG\Schema(
     *             @SWG\Property(property="merchant_id", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="url_cancel",
     *         required=true,
     *         in="body",
     *         description="Only for Magellan provider, set to none",
     *         @SWG\Schema(
     *             @SWG\Property(property="url_cancel", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="url_post_data",
     *         required=true,
     *         in="body",
     *         description="Only for Magellan provider, set to none",
     *         @SWG\Schema(
     *             @SWG\Property(property="url_post_data", type="string")
     *         )
     *     ),
     *     @SWG\Parameter(
     *         name="url_receipt",
     *         required=true,
     *         in="body",
     *         description="Only for Magellan provider, set to none",
     *         @SWG\Schema(
     *             @SWG\Property(property="url_receipt", type="string")
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A JSON file with an element 'content'.",
     *         @SWG\Schema(
     *             allOf={
     *                 @SWG\Definition(ref="#/definitions/Context"),
     *                 @SWG\Definition(
     *                     @SWG\Property(property="data", ref="#/definitions/TransactionData"),
     *                 )
     *             }
     *         )
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="retrieving data error",
     *         @SWG\Schema(ref="#/definitions/Error")
     *    )
     * )
     *
     * @param AbstractTransaction $transaction
     * @param Providers           $providers
     * @param Request             $request
     * @return View
     * @throws BadRequestHttpException|UndefinedProviderException
     */
    public function getInformationsAction(AbstractTransaction $transaction, Providers $providers, Request $request)
    {
        if (false === $this->uriSigner->check($request->getRequestUri())) {
            throw new BadRequestHttpException(
                static::CONTEXT,
                'Incoming request not correctly signed'
            );
        }

        $manager = $this->getDoctrine()->getManager();
        $hasTransaction = (bool) $manager
            ->getRepository(AbstractTransaction::class)
            ->findByReferenceId($transaction->getReferenceId())
        ;

        if ($hasTransaction) {
            throw $this->createNotFoundException(
                sprintf('A transaction with the reference_id "%s" already exists', $transaction->getReferenceId())
            );
        }

        $transactionHistory = new TransactionHistory(
            $transaction->getReferenceId(),
            MagellanStatusTransaction::INITIALIZED
        );

        $manager->persist($transaction);
        $manager->persist($transactionHistory);
        $manager->flush();

        $provider = $providers->getByTransaction($transaction);

        return $this->createView([
            'content' =>  $provider->retrievePaymentInformations($transaction)->getContent(),
        ]);
    }

    /**
     * @Rest\Post("/transaction/{provider}", name="_payment")
     * @ParamConverter(name="transactionStatus", converter="transaction_converter")
     * @SWG\Post(
     *     path="/transaction/{provider}",
     *     description="Update a transaction by reference_id.",
     *     operationId="UpdateTransactionByReferenceId",
     *     tags={"Payment"},
     *     @SWG\Parameter(
     *         name="provider",
     *         type="string",
     *         required=true,
     *         in="path",
     *         description="Payment provider"
     *     ),
     *     @SWG\Parameter(
     *         name="reference_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, external reference number transmitted by the client"
     *     ),
     *     @SWG\Parameter(
     *         name="result_label",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, Explanatory text on the back"
     *     ),
     *     @SWG\Parameter(
     *         name="transaction_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, Tracking transaction ID"
     *     ),
     *     @SWG\Parameter(
     *         name="auth_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, authorization number of the transaction"
     *     ),
     *     @SWG\Parameter(
     *         name="result_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, return code"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A JSON file with message result for payment.",
     *         @SWG\Schema(
     *             allOf={
     *                 @SWG\Definition(ref="#/definitions/Context"),
     *                 @SWG\Definition(
     *                     @SWG\Property(property="data", ref="#/definitions/TransactionResponse"),
     *                 )
     *             }
     *         )
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="not transaction found",
     *         @SWG\Schema(ref="#/definitions/Error")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="updating transaction error",
     *         @SWG\Schema(ref="#/definitions/Error")
     *     )
     * )
     *
     * @param StatusTransactionInterface $transactionStatus
     * @param StorageService $storageService
     * @param OrderProducer $producer
     * @return View
     */
    public function transactionAction(
        StatusTransactionInterface $transactionStatus,
        StorageService $storageService,
        OrderProducer $producer
    ) {
        $referenceId = $transactionStatus->getReferenceId();
        $manager = $this->getDoctrine()->getManager();

        $hasTransaction = (bool) $manager
            ->getRepository(AbstractTransaction::class)
            ->findByReferenceId($referenceId)
        ;

        if (false === $hasTransaction) {
            throw $this->createNotFoundException(
                sprintf('No transaction found with the reference_id "%s"', $referenceId)
            );
        }

        $manager->persist(new TransactionHistory($referenceId, $transactionStatus->getStatus()));
        $manager->flush();

        if ($this->retreiveReferencePrefix($referenceId) !== MagellanProvider::ECOM_PREFIX) {
            $keyRedis = sprintf('payment_%s', $referenceId);
            $redisData = $this->formatTransition(
                json_decode($storageService->getDataFromRedis($keyRedis), true),
                $transactionStatus->getStatus()
            );
            $producer->publish($redisData);
        }

        return $this->createView(['message' => $transactionStatus->getStatusMessage()]);
    }

    /**
     * @param string $referenceId
     * @return string
     */
    private function retreiveReferencePrefix(string $referenceId)
    {
        $data = explode('-', $referenceId);

        return $data[0];
    }

    /**
     * @Rest\Post("/receipt/{provider}", name="_payment")
     * @ParamConverter(name="transactionStatus", converter="transaction_converter")
     * @SWG\Post(
     *     path="/receipt/{provider}",
     *     description="Url receipt.",
     *     operationId="receiptTransactionUrl",
     *     tags={"Payment"},
     *     @SWG\Parameter(
     *         name="provider",
     *         type="string",
     *         required=true,
     *         in="path",
     *         description="Payment provider"
     *     ),
     *     @SWG\Parameter(
     *         name="reference_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, external reference number transmitted by the client"
     *     ),
     *     @SWG\Parameter(
     *         name="result_label",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, Explanatory text on the back"
     *     ),
     *     @SWG\Parameter(
     *         name="transaction_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, Tracking transaction ID"
     *     ),
     *     @SWG\Parameter(
     *         name="auth_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, authorization number of the transaction"
     *     ),
     *     @SWG\Parameter(
     *         name="result_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, return code"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A JSON file with message and origin front.",
     *         @SWG\Schema(
     *             allOf={
     *                 @SWG\Definition(ref="#/definitions/Context"),
     *                 @SWG\Definition(
     *                     @SWG\Property(property="data", ref="#/definitions/ReceiptResponse"),
     *                 )
     *             }
     *         )
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="not found",
     *         @SWG\Schema(ref="#/definitions/Error")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="updating transaction error",
     *         @SWG\Schema(ref="#/definitions/Error")
     *     )
     * )
     *
     * @param StatusTransactionInterface $transactionStatus
     * @return View
     */
    public function receiptAction(StatusTransactionInterface $transactionStatus)
    {
        $referenceId = $transactionStatus->getReferenceId();
        $manager = $this->getDoctrine()->getManager();

        /** @var AbstractTransaction $transaction */
        $transaction = $manager->getRepository(AbstractTransaction::class)->findOneByReferenceId($referenceId);

        if (!$transaction) {
            throw $this->createNotFoundException(
                sprintf('No transaction found with the reference_id "%s"', $referenceId)
            );
        }

        $response = [
            'reference_id' => $transaction->getReferenceId(),
            'origin' => $transaction->getOrigin(),
            'status' => $transactionStatus->getStatus(),
            'message' => $transactionStatus->getStatusMessage(),
        ];

        return $this->createView($response);
    }

    /**
     * @Rest\Get("/{referenceId}", name="_transaction")
     *
     * @SWG\Get(
     *     path="/{referenceId}",
     *     summary="Get a Transaction from referenceId",
     *     operationId="getTransactionByReferenceId",
     *     tags={"Transaction"},
     *     @SWG\Parameter(
     *         name="referenceId",
     *         in="path",
     *         type="string",
     *         required=true,
     *         description="referenceId"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Target one transaction by referenceId",
     *         @SWG\Schema(
     *             allOf={
     *                 @SWG\Definition(ref="#/definitions/Context"),
     *                 @SWG\Definition(
     *                     @SWG\Property(property="data", ref="#/definitions/TransactionComplete"),
     *                 )
     *             }
     *         )
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Transaction not found",
     *         @SWG\Schema(ref="#/definitions/Error")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *         @SWG\Schema(ref="#/definitions/Error")
     *     ),
     *   security={{ "bearer":{} }}
     * )
     *
     * @param string $referenceId
     * @return View
     */
    public function getAction($referenceId)
    {
        $manager = $this->getDoctrine()->getManager();

        $transaction = $manager
            ->getRepository(AbstractTransaction::class)
            ->findOneByReferenceId($referenceId);

        if (!$transaction) {
            throw new NotFoundHttpException(self::CONTEXT, 'Transaction not found');
        }

        $transactionHistory = $manager
            ->getRepository(TransactionHistory::class)
            ->getCurrentStatusByReferenceId($referenceId);

        if ($transactionHistory) {
            $transaction->setCurrentStatus($transactionHistory->getStatus());
        }

        return $this->createView($transaction);
    }

    /**
     * @param array $redisData
     * @param $status
     * @return array
     */
    private function formatTransition(array $redisData, $status)
    {
        $data = [
            'order' => $redisData['order'] ?? null,
            'transition' => (StatusTransactionInterface::OK === $status) ? self::FINALIZE : self::CANCEL,
            'transitionAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        return $data;
    }
}
