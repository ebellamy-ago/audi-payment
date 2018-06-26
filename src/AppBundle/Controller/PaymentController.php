<?php

namespace AppBundle\Controller;

use AppBundle\Entity\TransactionHistory;
use Mullenlowe\PayPluginBundle\Model\AbstractTransaction;
use Mullenlowe\PayPluginBundle\Model\StatusTransactionInterface;
use Mullenlowe\PayPluginBundle\Service\Provider\Providers;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class PaymentController extends Controller
{
    /**
     * @Route("/", methods={"POST"})
     * @ParamConverter(name="transaction", converter="transaction_converter")
     * @SWG\Post(
     *     path="/",
     *     description="Retrieve payments informations like a payment page.",
     *     @SWG\Parameter(
     *         name="provider",
     *         type="string",
     *         required=true,
     *         description="Name of the payment provider (ex: magellan).",
     *         in="query"
     *     ),
     *     @SWG\Parameter(
     *         name="currency",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="reference_id",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="amount",
     *         type="integer",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="lastname",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="phone",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_login",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_pwd",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_id",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="url_cancel",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="url_post_data",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="url_receipt",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A JSON file with an element 'content'."
     *     )
     * )
     */
    public function getPaymentInformationsAction(AbstractTransaction $transaction, Providers $providers)
    {
        $manager = $this->getDoctrine()->getManager();

        $manager->persist($transaction);
        $manager->flush();

        $provider = $providers->getByTransaction($transaction);

        return new JsonResponse([
            'content' =>  $provider->retrievePaymentInformations($transaction)->getContent()
        ]);
    }

    /**
     * @Route("/cancel/{provider}")
     * @ParamConverter(name="transactionStatus", converter="transaction_converter")
     * @SWG\Post(
     *     path="/cancel/{provider}",
     *     description="Cancel a transaction by reference_id.",
     *     @SWG\Parameter(
     *         name="provider",
     *         type="string",
     *         required=true,
     *         description="Name of the payment provider (ex: magellan).",
     *         in="path"
     *     ),
     *     @SWG\Parameter(
     *         name="reference_id",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="result_label",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="transaction_id",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="auth_code",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="result_code",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A JSON file with confirmation message for cancel."
     *     )
     * )
     */
    public function cancelAction(StatusTransactionInterface $transactionStatus)
    {
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

        return new JsonResponse(['message' => $transactionStatus->getStatusMessage()]);
    }
}
