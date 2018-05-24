<?php
/**
 * @brief		Member Sync
 *
 * @copyright	(c) 2001 - SVN_YYYY Invision Power Services, Inc.
 *
 * @package		IPS Social Suite
 * @subpackage	
 * @since		14 Jul 2014
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\forums\extensions\core\MemberSync;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Member Sync
 */
class _Forums
{
	/**
	 * Member is merged with another member
	 *
	 * @param	\IPS\Member	$member		Member being kept
	 * @param	\IPS\Member	$member2	Member being removed
	 * @return	void
	 */
	public function onMerge( $member, $member2 )
	{
		\IPS\Db::i()->update( 'forums_answer_ratings', array( 'member' => $member->member_id ), array( 'member=?', $member2->member_id ), array(), NULL, \IPS\Db::IGNORE );
		\IPS\Db::i()->update( 'forums_question_ratings', array( 'member' => $member->member_id ), array( 'member=?', $member2->member_id ), array(), NULL, \IPS\Db::IGNORE );
		\IPS\Db::i()->update( 'forums_rss_import', array( 'rss_import_mid' => $member->member_id ), array( 'rss_import_mid=?', $member2->member_id ) );
		
		if ( \IPS\Settings::i()->archive_on )
		{
			$archiveStorage = !\IPS\Settings::i()->archive_remote_sql_host ? \IPS\Db::i() : \IPS\Db::i( 'archive', array(
				'sql_host'		=> \IPS\Settings::i()->archive_remote_sql_host,
				'sql_user'		=> \IPS\Settings::i()->archive_remote_sql_user,
				'sql_pass'		=> \IPS\Settings::i()->archive_remote_sql_pass,
				'sql_database'	=> \IPS\Settings::i()->archive_remote_sql_database,
				'sql_port'		=> \IPS\Settings::i()->archive_sql_port,
				'sql_socket'	=> \IPS\Settings::i()->archive_sql_socket,
				'sql_tbl_prefix'=> \IPS\Settings::i()->archive_sql_tbl_prefix,
				'sql_utf8mb4'	=> isset( \IPS\Settings::i()->sql_utf8mb4 ) ? \IPS\Settings::i()->sql_utf8mb4 : FALSE
			) );
			$archiveStorage->update( 'forums_archive_posts', array( 'archive_author_id' => $member->member_id ), array( 'archive_author_id=?', $member2->member_id ) );
		}
	}
	
	/**
	 * Member is deleted
	 *
	 * @param	$member	\IPS\Member	The member
	 * @return	void
	 */
	public function onDelete( $member )
	{
		\IPS\Db::i()->delete( 'forums_answer_ratings', array( 'member=?', $member->member_id ) );
		\IPS\Db::i()->delete( 'forums_question_ratings', array( 'member=?', $member->member_id ) );
		\IPS\Db::i()->update( 'forums_rss_import', array( 'rss_import_mid' => 0 ), array( 'rss_import_mid=?', $member->member_id ) );
	}
}